<?php

namespace App\Services;

use App\Imports\EngineRowsImport;
use App\Models\Engine;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class EngineCatalogImportService
{
    private const MANUFACTURERS = ['MAN', 'MAN Marine', 'Volvo', 'Volvo Penta', 'Scania', 'Scania Marine'];

    private const TYPES = [
        'engine' => 'engine',
        'long_block' => 'long_block',
        'short_block' => 'short_block',
        'long block' => 'long_block',
        'short block' => 'short_block',
    ];

    public function importFile(UploadedFile $file): array
    {
        $reader = new EngineRowsImport();
        Excel::import($reader, $file);

        return $this->importRows($reader->rows);
    }

    /** Each row is validated and committed independently so one bad row cannot cancel good rows. */
    public function importRows(array $rows): array
    {
        $report = ['total' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($rows as $sourceRow) {
            $rowNumber = (int) ($sourceRow['number'] ?? ($report['total'] + 2));
            $values = is_array($sourceRow['values'] ?? null) ? $sourceRow['values'] : $sourceRow;
            if ($this->isBlankRow($values)) continue;

            $report['total']++;
            $sku = trim((string) ($values['sku'] ?? ''));
            try {
                $attributes = $this->validatedAttributes($values);
                DB::transaction(function () use ($attributes, &$report): void {
                    $engine = Engine::query()->where('sku', $attributes['sku'])->first();
                    $created = $engine === null;
                    $engine ??= new Engine(['slug' => $this->uniqueSlug($attributes['title'], $attributes['sku'])]);
                    $engine->fill($attributes)->save();

                    if ($created) $report['created']++;
                    else $report['updated']++;
                });
            } catch (\InvalidArgumentException $error) {
                $this->addError($report, $rowNumber, $sku, $error->getMessage());
            } catch (Throwable) {
                $this->addError($report, $rowNumber, $sku, 'не удалось сохранить строку');
            }
        }

        $report['skipped'] = count($report['errors']);
        return $report;
    }

    private function validatedAttributes(array $values): array
    {
        $sku = trim((string) ($values['sku'] ?? ''));
        $title = trim((string) ($values['title'] ?? ''));
        $manufacturerInput = trim((string) ($values['manufacturer'] ?? ''));
        $typeInput = Str::lower(trim((string) ($values['type'] ?? '')));

        if ($sku === '') throw new \InvalidArgumentException('отсутствует SKU');
        if ($title === '') throw new \InvalidArgumentException('отсутствует title');
        if (mb_strlen($sku) > 255) throw new \InvalidArgumentException('SKU длиннее 255 символов');
        if (mb_strlen($title) > 255) throw new \InvalidArgumentException('title длиннее 255 символов');

        $manufacturer = collect(self::MANUFACTURERS)->first(
            static fn(string $allowed) => mb_strtolower($allowed) === mb_strtolower($manufacturerInput)
        );
        if ($manufacturer === null) throw new \InvalidArgumentException('неизвестный manufacturer');
        if (!isset(self::TYPES[$typeInput])) throw new \InvalidArgumentException('неизвестный type');

        $attributes = [
            'sku' => $sku,
            'title' => $title,
            'manufacturer' => $manufacturer,
            'model' => $this->nullableString($values['model'] ?? null),
            'type' => self::TYPES[$typeInput],
            'price' => $this->price($values['price'] ?? null),
            'availability' => $this->nullableString($values['availability'] ?? null),
            'image' => $this->nullableString($values['image'] ?? null),
            'description' => $this->nullableString($values['description'] ?? null),
            'engine_family' => $this->nullableString($values['engine_family'] ?? null),
            'displacement' => $this->nullableString($values['displacement'] ?? null),
            'power' => $this->nullableString($values['power'] ?? null),
            'fuel_type' => $this->nullableString($values['fuel_type'] ?? null),
            'application' => $this->nullableString($values['application'] ?? null),
            'condition' => $this->nullableString($values['condition'] ?? null),
        ];

        $currency = $this->nullableString($values['currency'] ?? null);
        if ($currency !== null) {
            $currency = strtoupper($currency);
            if (!in_array($currency, ['EUR', 'USD', 'RUB'], true)) {
                throw new \InvalidArgumentException('currency должна быть EUR, USD или RUB');
            }
            $attributes['currency'] = $currency;
        }

        return $attributes;
    }

    private function price(mixed $value): ?string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') return null;

        $normalized = str_replace(',', '.', $raw);
        if (!preg_match('/^\d+(?:\.\d+)?$/D', $normalized)) {
            throw new \InvalidArgumentException('некорректный price');
        }

        try {
            $decimal = BigDecimal::of($normalized);
            if ($decimal->isGreaterThan('9999999999999.99')) {
                throw new \InvalidArgumentException('price превышает допустимый предел');
            }
            return (string) $decimal->toScale(2, RoundingMode::HALF_UP);
        } catch (\InvalidArgumentException $error) {
            throw $error;
        } catch (Throwable) {
            throw new \InvalidArgumentException('некорректный price');
        }
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null || !is_scalar($value)) return null;
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function isBlankRow(array $values): bool
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string) $value) !== '') return false;
        }
        return true;
    }

    private function uniqueSlug(string $title, string $sku): string
    {
        $base = Str::slug($title) ?: Str::slug($sku) ?: 'engine';
        $slug = $base;
        if (Engine::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(Str::slug($sku) ?: substr(sha1($sku), 0, 8));
        }
        $suffix = 2;
        $candidate = $slug;
        while (Engine::query()->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$suffix++;
        }
        return $candidate;
    }

    private function addError(array &$report, int $row, string $sku, string $reason): void
    {
        $report['errors'][] = [
            'row' => $row,
            'sku' => $sku !== '' ? $sku : null,
            'reason' => $reason,
        ];
    }
}
