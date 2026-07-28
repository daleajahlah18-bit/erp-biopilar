<?php

namespace App\Excel\Templates;

use App\Excel\Templates\Sheets\AssetInstructionSheet;
use App\Excel\Templates\Sheets\AssetTemplateSheet;
use App\Excel\Templates\Sheets\AssetReferenceSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterAssetTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        return [
            new AssetInstructionSheet(),
            new AssetTemplateSheet(),
            new AssetReferenceSheet()
        ];
    }
}
