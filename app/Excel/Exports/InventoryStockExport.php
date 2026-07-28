<?php

namespace App\Excel\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Excel\Exports\Sheets\InventoryStockDataSheet;
use App\Excel\Exports\Sheets\InventoryStockSummarySheet;

class InventoryStockExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new InventoryStockDataSheet($this->data),
            new InventoryStockSummarySheet($this->data),
        ];
    }
}
