<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\PartType;
use App\Models\Product;
use App\Models\ProductImport;
use App\Services\ProductImportService;
use App\Services\ProductPriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;
use ZipArchive;

class ImportReadyPricelists extends Command
{
    private const XML_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    protected $signature = 'products:import-ready
        {--dir=import_ready : Directory with xlsx pricelists}
        {--cbr=95 : Legacy alias for exchange-rate (EUR to RUB by default)}
        {--exchange-rate= : Overrides --cbr}
        {--currency= : Supplier currency; defaults to product_pricing.ready_currency}
        {--multiplier=2 : Price multiplier before FX}
        {--markup=2.5 : Percent markup after FX}
        {--batch=5000 : Upsert batch size}
        {--part-type=Запчасть : Default part type title}';

    protected $description = 'High-volume import for import_ready xlsx pricelists';

    private ?ProductImport $importRun = null;
    private array $pricing = [];

    public function handle(): int
    {
        set_time_limit(0);
        DB::disableQueryLog();

        $dir = base_path((string) $this->option('dir'));
        if (!is_dir($dir)) {
            $this->error("Directory not found: {$dir}");
            return self::FAILURE;
        }

        $this->pricing = [
            'supplier_currency' => $this->option('currency') ?: config('product_pricing.ready_currency'),
            'exchange_rate' => $this->option('exchange-rate') ?? $this->option('cbr'),
            'price_multiplier' => $this->option('multiplier'),
            'markup_percent' => $this->option('markup'),
        ];
        // Validate all parameters before the first write.
        app(ProductPriceService::class)->supplierAttributes(new \App\Models\Product(), '0', $this->pricing);
        $batchSize = max(500, (int) $this->option('batch'));

        $files = glob($dir . '/*.xlsx') ?: [];
        $files = array_values(array_filter($files, static fn(string $p) => !str_contains(basename($p), '~$')));
        if (empty($files)) {
            $this->warn('No xlsx files found.');
            return self::SUCCESS;
        }

        $totalUpserted = 0;
        $totalSkipped = 0;

        foreach ($files as $file) {
            $fileName = basename($file);
            $this->info("Processing: {$fileName}");
            $runs = app(ProductImportService::class);
            $this->importRun = $runs->start('ready_import', $fileName, metadata: $this->pricing);
            try {
                [$upserted, $skipped] = $this->importFile(
                    $file, $fileName, $this->pricing, $this->importRun, $batchSize, (string) $this->option('part-type')
                );
                $runs->finish($this->importRun);
            } catch (\Throwable $error) {
                $runs->finish($this->importRun, $error);
                throw $error;
            }
            $totalUpserted += $upserted;
            $totalSkipped += $skipped;
            $this->line("  upserted={$upserted}, skipped={$skipped}");
        }

        $this->newLine();
        $this->info("Completed. Total upserted={$totalUpserted}, skipped={$totalSkipped}");
        return $totalSkipped ? self::FAILURE : self::SUCCESS;
    }

    /** Preview uses the same row parser and price service without changing products or import history. */
    public function previewFile(string $filePath, array $pricing, int $limit = 50): array
    {
        app(ProductPriceService::class)->supplierAttributes(new Product(), '0', $pricing);
        $rows = [];
        $errors = [];
        $summary = $this->scanFile(
            $filePath,
            function (array $row) use (&$rows): void { $rows[] = $row; },
            function (int $rowNumber, string $reason) use (&$errors): void {
                if (count($errors) < 10) $errors[] = ['row' => $rowNumber, 'reason' => $reason];
            },
            $limit,
            500,
        );

        $existing = Product::query()->whereIn('sku', array_column($rows, 'sku'))->get()->keyBy('sku');
        $prices = app(ProductPriceService::class);
        $previewRows = [];
        foreach ($rows as $row) {
            $product = $existing->get($row['sku']) ?? new Product();
            $attributes = $prices->supplierAttributes($product, $row['supplier_price'], $pricing);
            $summary[$product->exists ? 'existing_sku' : 'new_sku']++;
            $previewRows[] = $row + [
                'currency' => $attributes['supplier_currency'],
                'exchange_rate' => $attributes['exchange_rate'],
                'price_multiplier' => $attributes['price_multiplier'],
                'markup_percent' => $attributes['markup_percent'],
                'automatic_price' => $attributes['automatic_price'],
                'final_price' => $attributes['selling_price'],
            ];
        }

        return ['rows' => $previewRows, 'summary' => $summary, 'errors' => $errors];
    }

    /** Called by both artisan and the admin queue job. */
    public function importFile(
        string $filePath,
        string $originalFilename,
        array $pricing,
        ProductImport $run,
        int $batchSize = 5000,
        string $partTypeTitle = 'Запчасть',
    ): array {
        app(ProductPriceService::class)->supplierAttributes(new Product(), '0', $pricing);
        $this->pricing = $pricing;
        $this->importRun = $run;

        $partType = PartType::query()->firstOrCreate(
            ['title' => $partTypeTitle],
            ['title' => $partTypeTitle, 'slug' => Str::slug($partTypeTitle)]
        );
        $brandTitle = $this->extractBrandFromFilename($originalFilename);
        $brand = Brand::query()->firstOrCreate(
            ['title' => $brandTitle],
            ['title' => $brandTitle, 'slug' => Str::slug($brandTitle)]
        );

        $buffer = [];
        $summary = $this->scanFile(
            $filePath,
            function (array $row) use (&$buffer, $brand, $partType, $batchSize): void {
                $now = now();
                $buffer[] = $row + [
                    'brand_id' => $brand->id,
                    'part_type_id' => $partType->id,
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
                if (count($buffer) >= $batchSize) {
                    $this->flushProducts($buffer);
                    $buffer = [];
                }
            },
            fn (int $rowNumber, string $reason) => $this->recordInvalidRow($rowNumber, $reason),
        );

        if ($buffer !== []) $this->flushProducts($buffer);
        return [$summary['valid_rows'], $summary['invalid_rows']];
    }

    /** Stops after 50 valid rows or 500 scanned data rows in preview mode. */
    private function scanFile(
        string $filePath,
        callable $onValid,
        callable $onInvalid,
        ?int $maxValid = null,
        ?int $maxScanned = null,
    ): array {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("Cannot open xlsx: {$filePath}");
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $zeroPaddingMasks = $this->readZeroPaddingMasks($zip);
        $hasSheet = $zip->locateName('xl/worksheets/sheet1.xml') !== false;
        $zip->close();

        if (!$hasSheet) {
            throw new RuntimeException("sheet1.xml not found in {$filePath}");
        }

        $headerMap = null;
        $headerRowNumber = null;
        $deliveryMsc = null;
        $summary = ['rows_scanned' => 0, 'valid_rows' => 0, 'invalid_rows' => 0, 'existing_sku' => 0, 'new_sku' => 0];

        $reader = new XMLReader();
        $realPath = realpath($filePath);
        if ($realPath === false || !$reader->open('zip://'.$realPath.'#xl/worksheets/sheet1.xml', null, LIBXML_NONET)) {
            throw new RuntimeException("Cannot read sheet1.xml in {$filePath}");
        }

        try {
        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'row') {
                continue;
            }

            $rowNumber = (int) ($reader->getAttribute('r') ?: 0);
            $rowXml = $reader->readOuterXML();
            $parsedRow = $this->parseRowCells($rowXml, $sharedStrings);
            $cells = $parsedRow['cells'];
            if ($headerMap !== null && $rowNumber > (int) $headerRowNumber && empty($cells)) {
                $summary['rows_scanned']++;
                if ($maxScanned !== null && $summary['rows_scanned'] >= $maxScanned) break;
                continue;
            }
            if (empty($cells)) {
                continue;
            }

            // metadata line: "Lead time to MSC:" in col A, value in col B
            if (($cells[1] ?? null) !== null && Str::lower((string) $cells[1]) === 'lead time to msc:') {
                $deliveryMsc = trim((string) ($cells[2] ?? ''));
            }

            if ($headerMap === null) {
                $candidate = $this->buildHeaderCandidate($cells);
                if (isset($candidate['mpn formatted']) && isset($candidate['name']) && isset($candidate['price msc'])) {
                    $headerMap = $candidate;
                    $headerRowNumber = $rowNumber;
                }
                continue;
            }

            if ($rowNumber <= (int) $headerRowNumber) {
                continue;
            }

            $summary['rows_scanned']++;

            $name = $this->cellByHeader($cells, $headerMap, ['name']);
            $skuColumn = $headerMap['mpn formatted'];
            $sku = (string) trim((string) ($cells[$skuColumn] ?? ''));
            $styleIndex = $parsedRow['styles'][$skuColumn] ?? null;
            if ($sku !== '' && $styleIndex !== null
                && in_array($parsedRow['types'][$skuColumn] ?? '', ['', 'n'], true)) {
                $maskLength = $zeroPaddingMasks[$styleIndex] ?? null;
                if ($maskLength !== null && preg_match('/^\d+$/D', $sku)) {
                    $sku = str_pad($sku, $maskLength, '0', STR_PAD_LEFT);
                }
            }
            $priceMscRaw = $this->cellByHeader($cells, $headerMap, ['price msc']);
            $replacement = $this->cellByHeader($cells, $headerMap, ['replacement']);

            if ($name === '' || $sku === '' || $priceMscRaw === '') {
                $summary['invalid_rows']++;
                $onInvalid($rowNumber, 'missing Name, Mpn formatted, or Price MSC');
                if ($maxScanned !== null && $summary['rows_scanned'] >= $maxScanned) break;
                continue;
            }

            $prices = app(ProductPriceService::class);
            try {
                $supplierPrice = $prices->decimal($priceMscRaw);
            } catch (\InvalidArgumentException) {
                $summary['invalid_rows']++;
                $onInvalid($rowNumber, 'invalid Price MSC');
                if ($maxScanned !== null && $summary['rows_scanned'] >= $maxScanned) break;
                continue;
            }

            $dimensions = [];
            $invalidDimension = null;
            foreach (['weight', 'width', 'height', 'length'] as $field) {
                $value = $this->cellByHeader($cells, $headerMap, [$field]);
                try {
                    $dimensions[$field] = $value === '' ? null : $prices->decimal($value, 6, 15);
                } catch (\InvalidArgumentException) {
                    $invalidDimension = $field;
                    break;
                }
            }
            if ($invalidDimension !== null) {
                $summary['invalid_rows']++;
                $onInvalid($rowNumber, 'invalid '.$invalidDimension);
                if ($maxScanned !== null && $summary['rows_scanned'] >= $maxScanned) break;
                continue;
            }

            $onValid([
                'sku' => $sku,
                'title' => Str::limit($name, 255, ''),
                'replacement' => $replacement !== '' ? $replacement : null,
                'delivery' => $deliveryMsc !== '' ? $deliveryMsc : null,
                'supplier_price' => $supplierPrice,
                ...$dimensions,
            ]);
            $summary['valid_rows']++;
            if (($maxValid !== null && $summary['valid_rows'] >= $maxValid)
                || ($maxScanned !== null && $summary['rows_scanned'] >= $maxScanned)) break;
        }
        } finally {
            $reader->close();
        }

        if ($headerMap === null) {
            throw new RuntimeException('Required ready pricelist headers Mpn formatted, Name, and Price MSC were not found.');
        }

        return $summary;
    }

    private function flushProducts(array $rows): void
    {
        app(ProductImportService::class)->writeBatch($rows, 'ready_import', $this->pricing, $this->importRun->id);
    }

    private function recordInvalidRow(int $rowNumber, string $reason): void
    {
        app(ProductImportService::class)->count($this->importRun->id, failed: 1);
        $metadata = $this->importRun->metadata ?? [];
        $metadata['errors'] ??= [];
        if (count($metadata['errors']) < 10) {
            $metadata['errors'][] = ['row' => $rowNumber, 'reason' => $reason];
            $this->importRun->update(['metadata' => $metadata]);
        }
        if ($this->output) $this->warn("Skipping row {$rowNumber}: {$reason}.");
    }

    private function buildHeaderCandidate(array $cells): array
    {
        $map = [];
        foreach ($cells as $col => $value) {
            $normalized = Str::lower(trim((string) $value));
            if ($normalized !== '') {
                $map[$normalized] = (int) $col;
            }
        }

        return $map;
    }

    private function cellByHeader(array $cells, array $headerMap, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($headerMap[$key])) {
                $col = $headerMap[$key];
                return trim((string) ($cells[$col] ?? ''));
            }
        }

        return '';
    }

    private function parseRowCells(string $rowXml, array $sharedStrings): array
    {
        $cells = [];
        $styles = [];
        $types = [];
        $row = simplexml_load_string($rowXml);
        if (!$row instanceof SimpleXMLElement) {
            return ['cells' => $cells, 'styles' => $styles, 'types' => $types];
        }

        $row->registerXPathNamespace('x', self::XML_NS);
        $cellsXml = $row->xpath('./x:c');
        if (!is_array($cellsXml)) {
            return ['cells' => $cells, 'styles' => $styles, 'types' => $types];
        }

        foreach ($cellsXml as $cell) {
            $ref = (string) $cell->attributes()->r;
            $type = (string) $cell->attributes()->t;
            $style = (string) $cell->attributes()->s;
            $col = $this->columnIndexFromRef($ref);
            if ($col < 1) {
                continue;
            }

            $value = '';
            $cellNs = $cell->children(self::XML_NS);
            if ($type === 's') {
                $idx = isset($cellNs->v) ? (int) (string) $cellNs->v : -1;
                $value = $sharedStrings[$idx] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = isset($cellNs->is->t) ? (string) $cellNs->is->t : '';
            } else {
                $value = isset($cellNs->v) ? (string) $cellNs->v : '';
            }

            $cells[$col] = $value;
            $styles[$col] = $style === '' ? null : (int) $style;
            $types[$col] = $type;
        }

        return ['cells' => $cells, 'styles' => $styles, 'types' => $types];
    }

    /** Maps Excel cell style indexes to simple numeric formats such as 000000. */
    private function readZeroPaddingMasks(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/styles.xml');
        if ($xml === false) return [];

        $styles = simplexml_load_string($xml);
        if (!$styles instanceof SimpleXMLElement) return [];
        $styles->registerXPathNamespace('x', self::XML_NS);

        $formats = [];
        foreach ($styles->xpath('./x:numFmts/x:numFmt') ?: [] as $format) {
            $formats[(int) $format->attributes()->numFmtId] = (string) $format->attributes()->formatCode;
        }

        $masks = [];
        foreach ($styles->xpath('./x:cellXfs/x:xf') ?: [] as $index => $xf) {
            $formatCode = $formats[(int) $xf->attributes()->numFmtId] ?? '';
            $integerFormat = explode(';', $formatCode, 2)[0];
            if (preg_match('/^0+$/D', $integerFormat)) {
                $masks[$index] = strlen($integerFormat);
            }
        }

        return $masks;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $reader = new XMLReader();
        $reader->XML($xml);
        $strings = [];

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'si') {
                continue;
            }

            $siXml = $reader->readOuterXML();
            preg_match_all('/<t(?:\\s[^>]*)?>(.*?)<\\/t>/s', $siXml, $matches);
            $parts = array_map(
                static fn(string $text): string => html_entity_decode($text, ENT_QUOTES | ENT_XML1),
                $matches[1] ?? []
            );
            $strings[] = implode('', $parts);
        }

        $reader->close();
        return $strings;
    }

    private function columnIndexFromRef(string $cellRef): int
    {
        if (!preg_match('/^([A-Z]+)/', strtoupper($cellRef), $m)) {
            return 0;
        }

        $letters = $m[1];
        $result = 0;
        for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
            $result = $result * 26 + (ord($letters[$i]) - 64);
        }

        return $result;
    }

    private function extractBrandFromFilename(string $fileName): string
    {
        if (preg_match('/^Pricelist\s+(.+?)\s+\d{2}\.\d{4}/i', $fileName, $m)) {
            return trim($m[1]);
        }

        return 'Unknown';
    }
}
