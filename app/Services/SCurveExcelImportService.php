<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\UploadedFile;
use App\Models\Project;
use Illuminate\Support\Carbon;

class SCurveExcelImportService
{
    public function parse(UploadedFile $file, $projectId, $name, $startDate, $endDate)
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheetByName('S-Curve Import');

        if (!$sheet) {
            throw new \Exception("File ini bukan Official ERP S-Curve Template (Worksheet 'S-Curve Import' tidak ditemukan). Silakan download template terlebih dahulu.");
        }

        // 1. Metadata Validation
        $templateId = trim((string)$sheet->getCell('A1')->getCalculatedValue());
        $templateVersion = trim((string)$sheet->getCell('A2')->getCalculatedValue());
        
        if ($templateId !== 'ERP_S_CURVE_TEMPLATE') {
            throw new \Exception("Invalid S-Curve Template. This file is not an official ERP S-Curve template.");
        }

        $metaProjectId = (int)$sheet->getCell('A3')->getCalculatedValue();
        $metaProjectNumber = trim((string)$sheet->getCell('A4')->getCalculatedValue());
        $metaProjectName = trim((string)$sheet->getCell('A5')->getCalculatedValue());
        $metaStartDate = trim((string)$sheet->getCell('A6')->getCalculatedValue());
        $metaEndDate = trim((string)$sheet->getCell('A7')->getCalculatedValue());
        $metaTotalWeek = (int)$sheet->getCell('A8')->getCalculatedValue();

        $project = Project::find($projectId);
        
        if ($metaProjectId !== $project->id && $metaProjectNumber !== $project->contract_number) {
            throw new \Exception("❌ Project mismatch!\nSelected Project: {$project->contract_number}\nTemplate Project: {$metaProjectNumber}");
        }

        $inputTotalWeeks = \App\Models\ProjectSCurve::calculateProjectWeeks($startDate, $endDate);
        if ((int)$metaTotalWeek !== (int)$inputTotalWeeks) {
            throw new \Exception("❌ Week count mismatch!\nProject Duration: {$inputTotalWeeks} Weeks\nTemplate Duration: {$metaTotalWeek} Weeks");
        }

        // 1.5 Header Structure Validation
        $headerRow = 11;
        $expectedWeeks = range(1, (int)$inputTotalWeeks);
        $detectedWeeks = [];
        $colIdx = 6; // F

        for ($w = 1; $w <= $metaTotalWeek; $w++) {
            $planHeader = trim((string)$sheet->getCellByColumnAndRow($colIdx, $headerRow)->getCalculatedValue());
            $actualHeader = trim((string)$sheet->getCellByColumnAndRow($colIdx + 1, $headerRow)->getCalculatedValue());
            
            if ($planHeader === "W{$w} Plan" && $actualHeader === "W{$w} Actual") {
                $detectedWeeks[] = $w;
            }
            $colIdx += 2;
        }

        if ($detectedWeeks !== $expectedWeeks) {
            $expectedStr = "W1 - W{$inputTotalWeeks}";
            $detectedStr = empty($detectedWeeks) ? "None" : "W" . min($detectedWeeks) . " - W" . max($detectedWeeks);
            $missing = array_diff($expectedWeeks, $detectedWeeks);
            $missingStr = empty($missing) ? "" : "\nMissing:\nW" . implode(", W", $missing);
            
            throw new \Exception("❌ Week structure mismatch\n\nExpected:\n{$expectedStr}\n\nDetected:\n{$detectedStr}{$missingStr}");
        }

        // 2. Data Extraction
        $highestRow = $sheet->getHighestDataRow();
        $items = [];
        $startRow = $headerRow + 1;
        
        // Find maximum column we need to read
        // B, C, D, E are basic (4 columns) + (TotalWeeks * 2)
        $maxColIndex = 5 + ($metaTotalWeek * 2);

        for ($row = $startRow; $row <= $highestRow; $row++) {
            $wbs = trim((string)$sheet->getCellByColumnAndRow(2, $row)->getValue()); // B
            
            // Kolom C dibaca, namun kita tetap men-generate secara dinamis untuk menjamin kebenaran hirarki
            $parts = explode('.', $wbs);
            if (count($parts) > 1) {
                array_pop($parts);
                $parentWbs = implode('.', $parts);
            } else {
                $parentWbs = ''; // root
            }

            $name = trim((string)$sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue()); // D
            $weightVal = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue(); // E
            
            if ($wbs === '' && $name === '') {
                continue; // Skip empty rows
            }

            if (strtoupper($name) === 'TOTAL' || strtoupper($wbs) === 'TOTAL') {
                break; // End of WBS section
            }

            $weight = $this->cleanPercentage($weightVal);

            $plan = [];
            $actual = [];
            $colIdx = 6; // F
            
            for ($w = 1; $w <= $metaTotalWeek; $w++) {
                $planVal = $sheet->getCellByColumnAndRow($colIdx, $row)->getCalculatedValue();
                $actualVal = $sheet->getCellByColumnAndRow($colIdx + 1, $row)->getCalculatedValue();
                
                if (is_numeric($planVal)) {
                    $plan[$w] = $this->cleanPercentage($planVal);
                }
                
                if (is_numeric($actualVal)) {
                    $actual[$w] = $this->cleanPercentage($actualVal);
                }
                
                $colIdx += 2;
            }

            $items[] = [
                'row' => $row,
                'code' => $wbs,
                'parent_code' => $parentWbs,
                'name' => $name,
                'weight' => $weight,
                'plan' => $plan,
                'actual' => $actual,
                'is_parent' => false, // Will calculate later
                'children' => []
            ];
        }

        // 3. Build Hierarchy
        $hierarchy = $this->buildHierarchy($items);

        $data = [
            'project_id' => $projectId,
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'weeks' => $metaTotalWeek,
            'sheet_name' => $sheet->getTitle(),
            'items' => $hierarchy,
            'flat_items' => $items, // Keep flat for DB insertion
        ];
        
        $data['validation'] = $this->validateParsedData($data);

        \Illuminate\Support\Facades\Log::info('SCurve Week Validation', [
            'project_id' => $project->id,
            'project_start_date' => $project->project_start_date,
            'project_end_date' => $project->project_end_date,
            'calculated_project_weeks' => $inputTotalWeeks,
            'template_total_weeks_raw' => $sheet->getCell('A8')->getValue(),
            'template_total_weeks_type' => gettype($sheet->getCell('A8')->getCalculatedValue()),
            'template_total_weeks_normalized' => (int) $metaTotalWeek,
            'detected_week_numbers' => $detectedWeeks,
            'detected_week_count' => count($detectedWeeks),
            'expected_week_columns' => "W1-W" . $inputTotalWeeks,
            'week_validation' => 'PASS'
        ]);

        return $data;
    }

    private function cleanPercentage($val)
    {
        if ($val === null || $val === '') return 0;
        if (is_numeric($val)) {
            // Excel often stores percentages as decimals (e.g. 10% = 0.1)
            // But user might type 10 directly based on instruction. 
            // We'll trust what's strictly provided. If it's < 1, assume it's a decimal percentage if total is low, 
            // but actually let's strictly use numeric values as percentages. (10 = 10%)
            return round(floatval($val), 4);
        }
        
        $str = str_replace('%', '', (string)$val);
        $str = str_replace(',', '.', $str);
        if (is_numeric(trim($str))) {
            return round(floatval(trim($str)), 4);
        }
        return 0;
    }

    private function buildHierarchy(array &$flatItems)
    {
        $hierarchy = [];
        $itemsByCode = [];

        foreach ($flatItems as &$item) {
            $itemsByCode[(string)$item['code']] = &$item;
        }

        foreach ($flatItems as &$item) {
            $parentCode = (string)$item['parent_code'];
            if ($parentCode !== '') {
                if (isset($itemsByCode[$parentCode])) {
                    $itemsByCode[$parentCode]['children'][] = &$item;
                    $itemsByCode[$parentCode]['is_parent'] = true;
                }
                // Jika parentCode !== '' tetapi tidak ditemukan di $itemsByCode, 
                // KITA TIDAK MEMASUKKANNYA KE DALAM ROOT. Ia akan ditangkap oleh validasi.
            } else {
                $hierarchy[] = &$item;
            }
        }

        return $hierarchy;
    }

    private function validateParsedData($data)
    {
        $errors = [];
        $totalLeafWeight = 0;
        $hasItems = count($data['flat_items']) > 0;

        if (!$hasItems) {
            $errors[] = "Tidak ada data WBS yang terdeteksi.";
            return [
                'is_valid' => false,
                'errors' => $errors,
                'total_leaf_weight' => 0
            ];
        }
        
        $allWbsCodes = array_column($data['flat_items'], 'code');

        foreach ($data['flat_items'] as $item) {
            // Validate Parent exists
            if ($item['parent_code'] !== '' && !in_array($item['parent_code'], $allWbsCodes)) {
                $errors[] = "WBS [{$item['code']}] memiliki Parent WBS [{$item['parent_code']}] yang tidak ditemukan di dokumen.";
            }

            if ($item['is_parent']) {
                // Check if Parent Weight = Sum of Children
                $childWeightSum = 0;
                foreach ($item['children'] as $child) {
                    $childWeightSum += $child['weight'];
                }
                
                if (abs($item['weight'] - $childWeightSum) > 0.05) {
                    $errors[] = "WBS [{$item['code']}] '{$item['name']}' memiliki Bobot {$item['weight']}% tetapi total anak-anaknya {$childWeightSum}%.";
                }
            } else {
                // Leaf Node
                $totalLeafWeight += $item['weight'];

                // Plan Sum = Leaf Weight
                $planSum = array_sum($item['plan']);
                if (abs($item['weight'] - $planSum) > 0.05) {
                    $errors[] = "WBS [{$item['code']}] '{$item['name']}' memiliki Bobot {$item['weight']}% tetapi total Plan mingguan adalah {$planSum}%.";
                }

                // Actual Cumulative <= Leaf Weight
                $actualSum = array_sum($item['actual']);
                if ($actualSum > $item['weight'] + 0.05) {
                    $errors[] = "WBS [{$item['code']}] '{$item['name']}' memiliki total Actual {$actualSum}% melebihi Bobot {$item['weight']}%.";
                }
            }
        }

        if (abs($totalLeafWeight - 100) > 0.05) {
            $errors[] = "Total Bobot pekerjaan (leaf) adalah {$totalLeafWeight}%, harusnya 100%.";
        }

        return [
            'is_valid' => count($errors) === 0,
            'errors' => $errors,
            'total_leaf_weight' => round($totalLeafWeight, 2),
        ];
    }

    public function import($parsedData)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($parsedData) {
            $sCurve = \App\Models\ProjectSCurve::create([
                'project_id' => $parsedData['project_id'],
                'name' => $parsedData['name'],
                'start_date' => $parsedData['start_date'],
                'end_date' => $parsedData['end_date'],
            ]);

            $this->insertItems($sCurve, $parsedData['items'], null);

            return $sCurve;
        });
    }

    private function insertItems($sCurve, $items, $parentId = null)
    {
        $sortOrder = 1;
        foreach ($items as $itemData) {
            $item = \App\Models\ProjectSCurveItem::create([
                's_curve_id' => $sCurve->id,
                'parent_id' => $parentId,
                'work_code' => $itemData['code'],
                'work_name' => $itemData['name'],
                'weight_percentage' => $itemData['weight'],
                'sort_order' => $sortOrder++
            ]);

            if ($itemData['is_parent']) {
                $this->insertItems($sCurve, $itemData['children'], $item->id);
            } else {
                // Insert Plans
                foreach ($itemData['plan'] as $week => $val) {
                    \App\Models\ProjectSCurvePlan::create([
                        's_curve_item_id' => $item->id,
                        'week_number' => $week,
                        'planned_percentage' => $val,
                    ]);
                }
                
                // Insert Actuals
                foreach ($itemData['actual'] as $week => $val) {
                    if ($val > 0 || $val === 0 || $val === '0') { // ensure we import 0 if explicitly defined, or just all actuals
                        \App\Models\ProjectSCurveActual::create([
                            's_curve_id' => $sCurve->id,
                            's_curve_item_id' => $item->id,
                            'week_number' => $week,
                            'actual_percentage' => $val,
                        ]);
                    }
                }
            }
        }
    }
}
