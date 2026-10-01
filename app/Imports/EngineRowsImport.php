<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

/** Reads heading-based rows only; persistence is handled by EngineCatalogImportService. */
class EngineRowsImport implements OnEachRow, WithHeadingRow
{
    public array $rows = [];

    public function onRow(Row $row): void
    {
        $this->rows[] = [
            'number' => $row->getIndex(),
            'values' => $row->toArray(),
        ];
    }
}
