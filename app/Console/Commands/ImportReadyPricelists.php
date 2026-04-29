<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\PartType;
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
        {--cbr=95 : EUR to RUB exchange rate}
        {--multiplier=2 : Price multiplier before FX}
        {--markup=2.5 : Percent markup after FX}
        {--batch=5000 : Upsert batch size}
        {--part-type=Запчасть : Default part type title}';

    protected $description = 'High-volume import for import_ready xlsx pricelists';

    public function handle(): int
    {
        set_time_limit(0);
        DB::disableQueryLog();

        $dir = base_path((string) $this->option('dir'));
        if (!is_dir($dir)) {
            $this->error("Directory not found: {$dir}");
            return self::FAILURE;
        }

        $cbr = (float) $this->option('cbr');
        $multiplier = (float) $this->option('multiplier');
        $markupFactor = 1 + ((float) $this->option('markup') / 100);
        $batchSize = max(500, (int) $this->option('batch'));

        $partTypeTitle = (string) $this->option('part-type');
        $partType = PartType::query()->firstOrCreate(
            ['title' => $partTypeTitle],
            ['title' => $partTypeTitle, 'slug' => Str::slug($partTypeTitle)]
        );

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
            [$upserted, $skipped] = $this->importOneFile(
                $file,
                $cbr,
                $multiplier,
                $markupFactor,
                $batchSize,
                $partType->id
            );
            $totalUpserted += $upserted;
            $totalSkipped += $skipped;
            $this->line("  upserted={$upserted}, skipped={$skipped}");
        }

        $this->newLine();
        $this->info("Completed. Total upserted={$totalUpserted}, skipped={$totalSkipped}");
        return self::SUCCESS;
    }

    private function importOneFile(
        string $filePath,
        float $cbr,
        float $multiplier,
        float $markupFactor,
        int $batchSize,
        int $partTypeId
    ): array {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("Cannot open xlsx: {$filePath}");
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException("sheet1.xml not found in {$filePath}");
        }

        $brandTitle = $this->extractBrandFromFilename(basename($filePath));
        $brand = Brand::query()->firstOrCreate(
            ['title' => $brandTitle],
            ['title' => $brandTitle, 'slug' => Str::slug($brandTitle)]
        );

        $headerMap = null;
        $headerRowNumber = null;
        $deliveryMsc = null;
        $buffer = [];
        $upserted = 0;
        $skipped = 0;

        $reader = new XMLReader();
        $reader->XML($sheetXml);

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'row') {
                continue;
            }

            $rowNumber = (int) ($reader->getAttribute('r') ?: 0);
            $rowXml = $reader->readOuterXML();
            $cells = $this->parseRowCells($rowXml, $sharedStrings);
            if (empty($cells)) {
                continue;
            }

            // metadata line: "Lead time to MSC:" in col A, value in col B
            if (($cells[1] ?? null) !== null && Str::lower((string) $cells[1]) === 'lead time to msc:') {
                $deliveryMsc = trim((string) ($cells[2] ?? ''));
            }

            if ($headerMap === null) {
                $candidate = $this->buildHeaderCandidate($cells);
                if (isset($candidate['name']) && (isset($candidate['price msc']) || isset($candidate['price_msc']))) {
                    $headerMap = $candidate;
                    $headerRowNumber = $rowNumber;
                }
                continue;
            }

            if ($rowNumber <= (int) $headerRowNumber) {
                continue;
            }

            $name = $this->cellByHeader($cells, $headerMap, ['name']);
            $sku = $this->cellByHeader($cells, $headerMap, ['mpn formatted', 'mpn']);
            $priceMscRaw = $this->cellByHeader($cells, $headerMap, ['price msc', 'price_msc']);
            $minimalAmountRaw = $this->cellByHeader($cells, $headerMap, ['minimal amount', 'minimal_amount']);
            $replacement = $this->cellByHeader($cells, $headerMap, ['replacement']);

            if ($name === '' || $sku === '' || $priceMscRaw === '') {
                $skipped++;
                continue;
            }

            $priceMsc = (float) str_replace(',', '.', $priceMscRaw);
            $finalPrice = max(0, round($priceMsc * $multiplier * $cbr * $markupFactor, 2));
            $quantity = (int) preg_replace('/[^\d\-]/', '', $minimalAmountRaw);
            if ($quantity < 0) {
                $quantity = 0;
            }

            $now = now();
            $buffer[] = [
                'sku' => $sku,
                'title' => Str::limit($name, 255, ''),
                'brand_id' => $brand->id,
                'part_type_id' => $partTypeId,
                'description' => $replacement !== '' ? $replacement : null,
                'delivery' => $deliveryMsc !== '' ? $deliveryMsc : null,
                'price' => $finalPrice,
                'quantity' => $quantity,
                'updated_at' => $now,
                'created_at' => $now,
            ];

            if (count($buffer) >= $batchSize) {
                $this->flushProducts($buffer);
                $upserted += count($buffer);
                $buffer = [];
            }
        }

        $reader->close();

        if (!empty($buffer)) {
            $this->flushProducts($buffer);
            $upserted += count($buffer);
        }

        return [$upserted, $skipped];
    }

    private function flushProducts(array $rows): void
    {
        DB::table('products')->upsert(
            $rows,
            ['sku'],
            ['title', 'brand_id', 'part_type_id', 'description', 'delivery', 'price', 'quantity', 'updated_at']
        );
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
        $row = simplexml_load_string($rowXml);
        if (!$row instanceof SimpleXMLElement) {
            return $cells;
        }

        $row->registerXPathNamespace('x', self::XML_NS);
        $cellsXml = $row->xpath('./x:c');
        if (!is_array($cellsXml)) {
            return $cells;
        }

        foreach ($cellsXml as $cell) {
            $ref = (string) $cell->attributes()->r;
            $type = (string) $cell->attributes()->t;
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
        }

        return $cells;
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

