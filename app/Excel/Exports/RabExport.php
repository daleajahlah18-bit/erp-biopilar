<?php

namespace App\Excel\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Rab;

class RabExport implements WithMultipleSheets
{
    protected $rab;

    public function __construct(Rab $rab)
    {
        $this->rab = $rab;
    }

    public function sheets(): array
    {
        return [
            new \App\Excel\Templates\Sheets\RabExportSheet($this->rab)
        ];
    }
}
