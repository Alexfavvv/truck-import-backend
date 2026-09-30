<?php

namespace App\Services;

use App\Models\Product;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** All catalog price arithmetic and writes pass through this service. Decimal strings in/out. */
class ProductPriceService
{
    public function decimal(mixed $value, int $scale = 2, int $precision = 15): string
    {
        $text = str_replace(',', '.', trim((string) $value));
        if (!preg_match('/^\d+(?:\.\d+)?$/D', $text)) {
            throw new InvalidArgumentException('Expected a nonnegative decimal, got: '.$text);
        }
        $number = BigDecimal::of($text)->toScale($scale, RoundingMode::HALF_UP);
        $max = BigDecimal::of(str_repeat('9', $precision - $scale).'.'.str_repeat('9', $scale));
        if ($number->isGreaterThan($max)) {
            throw new InvalidArgumentException('Decimal exceeds supported precision.');
        }
        return (string) $number;
    }

    public function calculateSellingPrice(mixed $supplierPrice, mixed $exchangeRate = '1', mixed $multiplier = '1', mixed $markupPercent = '0'): string
    {
        $supplier = $this->decimal($supplierPrice);
        $rate = $this->factor($exchangeRate);
        $multiple = $this->factor($multiplier);
        $markup = $this->decimal($markupPercent, 8, 18);
        $result = BigDecimal::of($supplier)->multipliedBy($rate)->multipliedBy($multiple)
            ->multipliedBy(BigDecimal::of($markup)->dividedBy('100', 10)->plus('1'));
        return $this->decimal((string) $result);
    }

    private function factor(mixed $value): string
    {
        $value = $this->decimal($value, 8, 18);
        if (BigDecimal::of($value)->isZero()) {
            throw new InvalidArgumentException('Exchange rate and multiplier must be positive.');
        }
        return $value;
    }

    public function supplierAttributes(Product $product, mixed $price, array $parameters): array
    {
        $currency = strtoupper(trim((string) ($parameters['supplier_currency'] ?? '')));
        if (!preg_match('/^[A-Z]{3}$/D', $currency)) {
            throw new InvalidArgumentException('Supplier currency must be explicitly specified as a three-letter code.');
        }
        $fields = [
            'supplier_price' => $this->decimal($price),
            'supplier_currency' => $currency,
            'exchange_rate' => $this->factor($parameters['exchange_rate'] ?? '1'),
            'price_multiplier' => $this->factor($parameters['price_multiplier'] ?? '1'),
            'markup_percent' => $this->decimal($parameters['markup_percent'] ?? '0', 8, 18),
        ];
        $calculated = $this->calculateSellingPrice($fields['supplier_price'], $fields['exchange_rate'], $fields['price_multiplier'], $fields['markup_percent']);
        $fields['automatic_price'] = $calculated;
        $fields['selling_price'] = $product->manual_price !== null ? $this->decimal($product->manual_price) : $calculated;
        $fields['price'] = $fields['selling_price'];
        return $fields;
    }

    /** Legacy cena is already the catalog sale price, not a supplier cost. */
    public function saleAttributes(Product $product, mixed $price): array
    {
        $automatic = $this->decimal($price);
        $selling = $product->manual_price !== null ? $this->decimal($product->manual_price) : $automatic;
        return [
            'supplier_price' => null,
            'supplier_currency' => null,
            'exchange_rate' => null,
            'price_multiplier' => null,
            'markup_percent' => null,
            'automatic_price' => $automatic,
            'selling_price' => $selling,
            'price' => $selling,
        ];
    }

    public function applySalePrice(Product $product, mixed $price, string $source = 'import', ?int $userId = null, ?int $importId = null): Product
    {
        return $this->persist($product, fn (Product $locked) => $this->saleAttributes($locked, $price), $source, $userId, $importId);
    }

    public function applySupplierPrice(Product $product, mixed $price, array $parameters, string $source = 'import', ?int $userId = null, ?int $importId = null): Product
    {
        return $this->persist($product, fn (Product $locked) => $this->supplierAttributes($locked, $price, $parameters), $source, $userId, $importId);
    }

    public function setManualPrice(Product $product, mixed $price, ?int $userId = null): Product
    {
        $price = $this->decimal($price);
        return $this->persist($product, fn () => ['manual_price' => $price, 'selling_price' => $price, 'price' => $price], 'manual', $userId);
    }

    public function clearManualPrice(Product $product, ?int $userId = null): Product
    {
        return $this->persist($product, function (Product $locked) {
            $price = $locked->automatic_price;
            if ($price === null) {
                throw new InvalidArgumentException('Automatic price is unavailable until a price has been imported.');
            }
            return ['manual_price' => null, 'selling_price' => $price, 'price' => $price];
        }, 'manual', $userId);
    }

    private function persist(Product $product, callable $pricing, string $source, ?int $userId = null, ?int $importId = null): Product
    {
        return DB::transaction(function () use ($product, $pricing, $source, $userId, $importId) {
            $locked = $product->exists ? Product::query()->lockForUpdate()->findOrFail($product->id) : new Product();
            $old = $this->snapshot($locked);
            // Keep current locked pricing; apply only the caller's non-price changes.
            $dirty = array_diff_key($product->getDirty(), array_flip([
                'price', 'supplier_price', 'automatic_price', 'selling_price', 'manual_price', 'supplier_currency',
                'exchange_rate', 'price_multiplier', 'markup_percent', 'price_updated_at',
            ]));
            $locked->fill($dirty);
            $locked->fill($pricing($locked));
            $history = $this->history($old, $locked, $source, $userId, $importId);
            if ($history !== null) $locked->price_updated_at = now();
            $locked->save();
            if ($history !== null) DB::table('product_price_history')->insert(['product_id' => $locked->id] + $history);
            $product->setRawAttributes($locked->getAttributes(), true);
            $product->exists = true;
            return $product;
        });
    }

    public function snapshot(Product $product): array
    {
        return [
            'supplier_price' => $product->supplier_price,
            'automatic_price' => $product->automatic_price,
            'selling_price' => $product->selling_price ?? ($product->exists ? $this->decimal($product->getRawOriginal('price')) : null),
            'manual_price' => $product->manual_price,
        ];
    }

    public function history(array $old, Product $product, string $source, ?int $userId, ?int $importId): ?array
    {
        $new = $this->snapshot($product);
        if ($old === $new) return null;
        $row = [
            'product_sku' => $product->sku,
            'source' => $source,
            'user_id' => $userId,
            'product_import_id' => $importId,
            'created_at' => now(),
        ];
        foreach ($new as $key => $value) {
            $row['old_'.$key] = $old[$key];
            $row['new_'.$key] = $value;
        }
        return $row;
    }
}
