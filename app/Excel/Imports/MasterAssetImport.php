<?php

namespace App\Excel\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class MasterAssetImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new MasterAssetSheetImport();
    }

    public function sheets(): array
    {
        // Sheet 1 is the Template (0 is Instruction, 2 is Reference)
        return [
            1 => $this->sheetImport,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Ignore other sheets
    }
}
