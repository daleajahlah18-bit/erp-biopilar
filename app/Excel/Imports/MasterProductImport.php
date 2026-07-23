<?php

namespace App\Excel\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class MasterProductImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new MasterProductSheetImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->sheetImport,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Skip unknown sheets (e.g. Reference sheet)
    }
}
