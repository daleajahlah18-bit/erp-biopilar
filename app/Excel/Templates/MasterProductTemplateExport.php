<?php

namespace App\Excel\Templates;

use App\Excel\Templates\Sheets\ProductTemplateSheet;
use App\Excel\Templates\Sheets\ReferenceSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterProductTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        return [
            new ProductTemplateSheet(),
            new ReferenceSheet()
        ];
    }
}
