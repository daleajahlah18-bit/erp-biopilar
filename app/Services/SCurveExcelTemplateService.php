<?php

namespace App\Services;

use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Illuminate\Support\Carbon;

class SCurveExcelTemplateService
{
    public function generateTemplate(Project $project)
    {
        $spreadsheet = new Spreadsheet();
        
        // Remove default sheet
        $spreadsheet->removeSheetByIndex(0);
        
        // Calculate weeks using centralized method
        $totalWeeks = \App\Models\ProjectSCurve::calculateProjectWeeks($project->project_start_date, $project->project_end_date);

        // 1. Instructions Sheet (Hidden or Visible depending on preference, we make it visible)
        $this->createInstructionsSheet($spreadsheet);

        // 2. Data Sheet
        $this->createDataSheet($spreadsheet, $project, $totalWeeks);

        return $spreadsheet;
    }

    private function createInstructionsSheet(Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Instructions');
        
        $instructions = [
            "S-CURVE IMPORT TEMPLATE",
            "",
            "1. Template ini dibuat khusus untuk project yang dipilih.",
            "2. Jangan mengubah nama worksheet.",
            "3. Jangan mengubah header.",
            "4. Jangan menghapus metadata template.",
            "5. Jangan merge cell pada area WBS.",
            "6. Parent WBS menggunakan format 1, 2, 3, dst.",
            "7. Child WBS menggunakan format 1.1, 1.2, 2.1, dst.",
            "8. Total Leaf Weight harus = 100%.",
            "9. Total Child Weight harus = Parent Weight.",
            "10. Weekly Plan total harus = Leaf Weight.",
            "11. Weekly Actual adalah progress mingguan (bukan cumulative).",
            "12. Actual menggunakan angka tanpa simbol %.",
            "13. Cumulative akan dihitung otomatis oleh ERP.",
            "14. Jangan mengubah Project Number.",
            "15. Jangan mengubah Total Week.",
            "16. Upload kembali file dalam format .xlsx."
        ];

        $row = 1;
        foreach ($instructions as $text) {
            $sheet->setCellValue('A' . $row, $text);
            $row++;
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getColumnDimension('A')->setWidth(60);

        // Metadata section
        $row += 2;
        $sheet->setCellValue('A' . $row, '--- SYSTEM METADATA ---');
        $sheet->setCellValue('A' . ($row + 1), 'ERP_S_CURVE_TEMPLATE');
        $sheet->setCellValue('A' . ($row + 2), 'VERSION: 1.0');
        
        // Protect Instructions sheet
        $sheet->getProtection()->setSheet(true);
    }

    private function createDataSheet(Spreadsheet $spreadsheet, Project $project, int $totalWeeks)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('S-Curve Import');
        
        // Set metadata on A1..A7 (Hidden)
        $sheet->setCellValue('A1', 'ERP_S_CURVE_TEMPLATE');
        $sheet->setCellValue('A2', '1.0');
        $sheet->setCellValue('A3', $project->id);
        $sheet->setCellValue('A4', $project->contract_number);
        $sheet->setCellValue('A5', $project->project_name);
        $sheet->setCellValue('A6', Carbon::parse($project->project_start_date)->format('Y-m-d'));
        $sheet->setCellValue('A7', Carbon::parse($project->project_end_date)->format('Y-m-d'));
        $sheet->setCellValue('A8', $totalWeeks);
        
        // Project Info Section
        $sheet->setCellValue('B2', 'S-CURVE PROJECT PROGRESS IMPORT');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(16);
        
        $sheet->setCellValue('B4', 'Project Number');
        $sheet->setCellValue('C4', $project->contract_number);
        
        $sheet->setCellValue('B5', 'Project Name');
        $sheet->setCellValue('C5', $project->project_name);
        
        $sheet->setCellValue('B6', 'Start Date');
        $sheet->setCellValue('C6', Carbon::parse($project->project_start_date)->format('d/m/Y'));
        
        $sheet->setCellValue('B7', 'End Date');
        $sheet->setCellValue('C7', Carbon::parse($project->project_end_date)->format('d/m/Y'));
        
        $sheet->setCellValue('B8', 'Total Week');
        $sheet->setCellValue('C8', $totalWeeks);

        $sheet->getStyle('B4:B8')->getFont()->setBold(true);
        $sheet->getStyle('B4:C8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');

        // WBS Table Header
        $headerRow = 11;
        $sheet->setCellValue('B' . ($headerRow - 1), 'WBS WORK BREAKDOWN STRUCTURE');
        $sheet->getStyle('B' . ($headerRow - 1))->getFont()->setBold(true)->setSize(12);

        $headers = ['WBS', 'Parent WBS', 'Jenis Pekerjaan', 'Bobot (%)'];
        
        $colIndex = 2; // B
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, $headerRow, $header);
            $sheet->getColumnDimensionByColumn($colIndex)->setWidth($colIndex === 4 ? 40 : 15);
            $colIndex++;
        }
        
        // Generate Weekly columns
        for ($w = 1; $w <= $totalWeeks; $w++) {
            $sheet->setCellValueByColumnAndRow($colIndex, $headerRow, 'W' . $w . ' Plan');
            $sheet->getColumnDimensionByColumn($colIndex)->setWidth(12);
            $colIndex++;
            
            $sheet->setCellValueByColumnAndRow($colIndex, $headerRow, 'W' . $w . ' Actual');
            $sheet->getColumnDimensionByColumn($colIndex)->setWidth(12);
            $colIndex++;
        }

        // Style Header
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);
        $headerRange = 'B' . $headerRow . ':' . $lastCol . $headerRow;
        
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F81BD']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Add some blank rows for input
        $inputStartRow = $headerRow + 1;
        $inputEndRow = $inputStartRow + 50; // provide 50 blank rows

        // Apply borders to input area
        $sheet->getStyle('B' . $inputStartRow . ':' . $lastCol . $inputEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]
            ]
        ]);

        // Hide Metadata Column A
        $sheet->getColumnDimension('A')->setVisible(false);

        // Protect the sheet, but allow editing in input area
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setInsertRows(true);
        $sheet->getProtection()->setDeleteRows(true);
        
        // Unprotect input cells
        $sheet->getStyle('B' . $inputStartRow . ':' . $lastCol . '1000')->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
    }
}
