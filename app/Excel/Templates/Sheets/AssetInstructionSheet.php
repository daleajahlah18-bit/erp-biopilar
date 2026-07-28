<?php

namespace App\Excel\Templates\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetInstructionSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            ['1', 'Asset Code', 'Kode unik asset. Kosongkan jika ingin membuat asset baru, isi jika ingin update asset yang ada.'],
            ['2', 'Asset Name', 'Nama asset (Wajib diisi).'],
            ['3', 'Category', 'Kategori asset sesuai nama di sistem. Tidak case sensitive (Wajib diisi).'],
            ['4', 'Brand', 'Merk atau brand dari asset (Opsional).'],
            ['5', 'Model', 'Model atau tipe spesifik asset (Opsional).'],
            ['6', 'Serial Number', 'Nomor seri pabrik (Opsional).'],
            ['7', 'Location', 'Lokasi penyimpanan asset (Wajib diisi).'],
            ['8', 'Department', 'Departemen yang memegang asset (Wajib diisi).'],
            ['9', 'Responsible Person', 'PIC atau penanggung jawab (Email/Nama Karyawan) (Wajib diisi).'],
            ['10', 'Purchase Date', 'Tanggal pembelian dengan format YYYY-MM-DD atau DD/MM/YYYY (Wajib diisi).'],
            ['11', 'Start Depreciation Date', 'Tanggal mulai disusutkan, format YYYY-MM-DD atau DD/MM/YYYY (Wajib diisi).'],
            ['12', 'Acquisition Cost', 'Harga perolehan asset dalam bentuk angka bulat, contoh: 5000000 (Wajib diisi).'],
            ['13', 'Residual Value', 'Nilai sisa atau nilai residu (Wajib diisi).'],
            ['14', 'Commercial Method', 'Metode penyusutan komersial (Straight Line / Double Declining Balance) (Wajib diisi).'],
            ['15', 'Commercial Useful Life', 'Masa manfaat komersial dalam hitungan BULAN (Wajib diisi).'],
            ['16', 'Fiscal Method', 'Metode penyusutan fiskal (Straight Line / Double Declining Balance) (Wajib diisi).'],
            ['17', 'Fiscal Useful Life', 'Masa manfaat fiskal dalam hitungan BULAN (Wajib diisi).'],
        ]);
    }

    public function headings(): array
    {
        return [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT MASTER ASSET'],
            ['PERINGATAN PENTING: JANGAN MENGUBAH, MENGHAPUS, ATAU MENAMBAH KOLOM (HEADER) PADA SHEET "Asset Template"'],
            [''],
            ['No', 'Kolom', 'Keterangan']
        ];
    }

    public function title(): string
    {
        return 'Instruction';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');

        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(100);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFF0000']]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
