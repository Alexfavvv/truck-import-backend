<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PartSearchListImportService
{
    /**
     * @return array{ordered_skus: list<string>, quantities: array<string, int>, label_by_norm: array<string, string>}
     */
    public function parseUploadedFile(UploadedFile $file): array
    {
        $path = $file->getRealPath() ?: $file->getPathname();
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $orderedSkus = [];
        $quantities = [];
        $labelByNorm = [];
        $isFirst = true;

        foreach ($rows as $row) {
            $skuRaw = isset($row[0]) ? trim((string) $row[0]) : '';
            $qtyCell = $row[1] ?? null;

            if ($isFirst && $this->looksLikeHeaderRow($skuRaw, $qtyCell)) {
                $isFirst = false;

                continue;
            }
            $isFirst = false;

            if ($skuRaw === '') {
                continue;
            }

            $qty = $this->parseQuantity($qtyCell);
            $norm = mb_strtolower($skuRaw);

            if (! isset($quantities[$norm])) {
                $orderedSkus[] = $norm;
                $quantities[$norm] = 0;
                $labelByNorm[$norm] = $skuRaw;
            }
            $quantities[$norm] += $qty;
        }

        return [
            'ordered_skus' => $orderedSkus,
            'quantities' => $quantities,
            'label_by_norm' => $labelByNorm,
        ];
    }

    /**
     * @param  array{ordered_skus: list<string>, quantities: array<string, int>, label_by_norm?: array<string, string>}  $parsed
     * @return array{items: list<array{product: Product, quantity: int}>, not_found: list<string>}
     */
    public function resolveProducts(array $parsed): array
    {
        $orderedSkus = $parsed['ordered_skus'] ?? [];
        $quantities = $parsed['quantities'] ?? [];
        $labelByNorm = $parsed['label_by_norm'] ?? [];

        $items = [];
        $notFound = [];

        foreach ($orderedSkus as $norm) {
            $qty = (int) ($quantities[$norm] ?? 1);
            if ($qty < 1) {
                $qty = 1;
            }

            $product = Product::query()
                ->with('brand', 'partType')
                ->whereRaw('LOWER(TRIM(sku)) = ?', [$norm])
                ->orderBy('id')
                ->first();

            if (! $product) {
                $notFound[] = $labelByNorm[$norm] ?? $norm;

                continue;
            }

            $items[] = [
                'product' => $product,
                'quantity' => $qty,
            ];
        }

        return [
            'items' => $items,
            'not_found' => $notFound,
        ];
    }

    private function looksLikeHeaderRow(string $firstCell, mixed $secondCell): bool
    {
        $a = mb_strtolower($firstCell);
        if (str_contains($a, 'артикул') || $a === 'sku' || str_contains($a, 'код')) {
            return true;
        }
        if (is_string($secondCell)) {
            $b = mb_strtolower(trim($secondCell));
            if ($b !== '' && (str_contains($b, 'колич') || str_contains($b, 'кол-во') || str_contains($b, 'qty'))) {
                return true;
            }
        }

        return false;
    }

    private function parseQuantity(mixed $raw): int
    {
        if ($raw === null || $raw === '') {
            return 1;
        }
        if (is_numeric($raw)) {
            return max(1, (int) round((float) $raw));
        }
        $s = str_replace([' ', "\xc2\xa0"], ['', ''], (string) $raw);
        $s = str_replace(',', '.', $s);
        if (is_numeric($s)) {
            return max(1, (int) round((float) $s));
        }

        return 1;
    }
}
