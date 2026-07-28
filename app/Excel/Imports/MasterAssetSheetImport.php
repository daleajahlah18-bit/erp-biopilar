<?php

namespace App\Excel\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class MasterAssetSheetImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public $data;

    public function collection(Collection $rows)
    {
        $this->data = $rows;
    }

    public function headingRow(): int
    {
        return 1;
    }
}
