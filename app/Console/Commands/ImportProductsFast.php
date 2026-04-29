<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\PartType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportProductsFast extends Command
{
    protected $signature = 'products:import-fast
        {file : Absolute or relative path to CSV file}
        {--delimiter=auto : CSV delimiter: auto, comma, semicolon, tab}
        {--batch=5000 : Upsert batch size}';

    protected $description = 'High-volume products import from CSV using batched upsert';

    public function handle(): int
    {
        set_time_limit(0);
        DB::disableQueryLog();

        $path = $this->resolvePath((string) $this->argument('file'));
        if (!is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $batchSize = max(100, (int) $this->option('batch'));
        $delimiter = $this->resolveDelimiter((string) $this->option('delimiter'));
        $handle = fopen($path, 'rb');

        if (!$handle) {
            $this->error('Cannot open file for reading.');
            return self::FAILURE;
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            $this->error('CSV file is empty.');
            return self::FAILURE;
        }

        if ($delimiter === 'auto') {
            $delimiter = $this->detectDelimiter($firstLine);
        }

        rewind($handle);
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            $this->error('Failed to read CSV header.');
            return self::FAILURE;
        }

        $headerMap = $this->buildHeaderMap($header);
        $required = ['nazvanie_tovara', 'artikul', 'cena', 'kolicestvo', 'brend', 'tip_detali'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $headerMap)) {
                fclose($handle);
                $this->error("Missing required CSV column: {$key}");
                return self::FAILURE;
            }
        }

        $brands = Brand::query()->pluck('id', 'title')->all();
        $partTypes = PartType::query()->pluck('id', 'title')->all();

        $rowsBuffer = [];
        $processed = 0;
        $skipped = 0;
        $line = 1;

        $bar = $this->output->createProgressBar();
        $bar->start();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if ($this->isEmptyCsvRow($row)) {
                continue;
            }

            $payload = $this->rowToPayload($row, $headerMap, $brands, $partTypes);
            if ($payload === null) {
                $skipped++;
                continue;
            }

            $rowsBuffer[] = $payload;
            if (count($rowsBuffer) >= $batchSize) {
                $this->flushBatch($rowsBuffer);
                $processed += count($rowsBuffer);
                $rowsBuffer = [];
            }

            $bar->advance();
        }

        if (!empty($rowsBuffer)) {
            $this->flushBatch($rowsBuffer);
            $processed += count($rowsBuffer);
        }

        fclose($handle);
        $bar->finish();
        $this->newLine(2);

        $this->info("Done. Upserted: {$processed}. Skipped: {$skipped}.");
        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (Str::startsWith($path, ['/','./','../'])) {
            return realpath($path) ?: $path;
        }

        return base_path($path);
    }

    private function resolveDelimiter(string $option): string
    {
        return match ($option) {
            'comma' => ',',
            'semicolon' => ';',
            'tab' => "\t",
            default => 'auto',
        };
    }

    private function detectDelimiter(string $line): string
    {
        $candidates = [
            ';' => substr_count($line, ';'),
            ',' => substr_count($line, ','),
            "\t" => substr_count($line, "\t"),
        ];

        arsort($candidates);
        return (string) array_key_first($candidates);
    }

    private function buildHeaderMap(array $header): array
    {
        $map = [];
        foreach ($header as $idx => $column) {
            $key = trim(Str::lower((string) $column));
            if ($key !== '') {
                $map[$key] = $idx;
            }
        }

        return $map;
    }

    private function rowToPayload(array $row, array $headerMap, array &$brands, array &$partTypes): ?array
    {
        $title = $this->cell($row, $headerMap, 'nazvanie_tovara');
        $sku = $this->cell($row, $headerMap, 'artikul');
        $brandTitle = $this->cell($row, $headerMap, 'brend');
        $partTypeTitle = $this->cell($row, $headerMap, 'tip_detali');

        if ($title === '' || $sku === '' || $brandTitle === '' || $partTypeTitle === '') {
            return null;
        }

        $brandId = $brands[$brandTitle] ?? null;
        if (!$brandId) {
            $brand = Brand::query()->firstOrCreate(
                ['title' => $brandTitle],
                ['title' => $brandTitle, 'slug' => Str::slug($brandTitle)]
            );
            $brandId = $brand->id;
            $brands[$brandTitle] = $brandId;
        }

        $partTypeId = $partTypes[$partTypeTitle] ?? null;
        if (!$partTypeId) {
            $partType = PartType::query()->firstOrCreate(
                ['title' => $partTypeTitle],
                ['title' => $partTypeTitle, 'slug' => Str::slug($partTypeTitle)]
            );
            $partTypeId = $partType->id;
            $partTypes[$partTypeTitle] = $partTypeId;
        }

        $priceRaw = $this->cell($row, $headerMap, 'cena');
        $qtyRaw = $this->cell($row, $headerMap, 'kolicestvo');
        $description = $this->cell($row, $headerMap, 'opisanie');
        $delivery = $this->cell($row, $headerMap, 'vremia_dostavki');

        $price = (float) str_replace(',', '.', $priceRaw);
        $quantity = (int) preg_replace('/[^\d\-]/', '', $qtyRaw);
        $now = now();

        return [
            'sku' => $sku,
            'title' => Str::limit($title, 255, ''),
            'brand_id' => $brandId,
            'part_type_id' => $partTypeId,
            'description' => $description !== '' ? $description : null,
            'delivery' => $delivery !== '' ? $delivery : null,
            'price' => max(0, $price),
            'quantity' => max(0, $quantity),
            'updated_at' => $now,
            'created_at' => $now,
        ];
    }

    private function flushBatch(array $rows): void
    {
        DB::table('products')->upsert(
            $rows,
            ['sku'],
            ['title', 'brand_id', 'part_type_id', 'description', 'delivery', 'price', 'quantity', 'updated_at']
        );
    }

    private function cell(array $row, array $headerMap, string $key): string
    {
        $idx = $headerMap[$key] ?? null;
        if ($idx === null) {
            return '';
        }

        return trim((string) ($row[$idx] ?? ''));
    }

    private function isEmptyCsvRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}

