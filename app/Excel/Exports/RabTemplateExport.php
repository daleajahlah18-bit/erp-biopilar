<?php

namespace App\Excel\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RabTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new \App\Excel\Templates\Sheets\RabTemplateSheet()
        ];
    }
}
