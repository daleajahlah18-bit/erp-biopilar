<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>S-Curve {{ $sCurve->name }}</title>
    <style>
        @page { size: A4 landscape; margin: 5mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 2px; text-align: center; overflow: hidden; }
        th { background-color: #f0f0f0; font-weight: bold; font-size: 8px;}
        
        .header-table { border: none; margin-bottom: 5px; width: 100%; table-layout: auto; }
        .header-table td, .header-table th { border: none; padding: 2px; text-align: left; font-size: 10px;}
        
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .bg-highlight { background-color: #ffff00 !important; }
        .status-box { border: 1px solid #000; padding: 5px; background-color: #e0f7fa; font-size: 9px; width: 180px; float: right; }
        
        .grid-container { position: relative; width: 100%; }
        .overlay-chart { position: absolute; top: 0; left: 30%; width: 70%; height: 100%; z-index: 10; opacity: 0.8; pointer-events: none; }
        
        .no-col { width: 3%; }
        .wbs-col { width: 22%; }
        .weight-col { width: 5%; }
        /* Week cols take remaining 70% equally */
        
        .plan-val { font-size: 8px; color: #0044cc; display: block; border-bottom: 0.5px dotted #ccc; padding-bottom: 1px; margin-bottom: 1px; }
        .act-val { font-size: 8px; color: #cc0000; display: block; }
        
        .wbs-row { height: 20pt; }
        .wbs-row td { height: 20pt; overflow: hidden; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <h3 style="margin: 0; font-size: 14px;">PROJECT S-CURVE: {{ strtoupper($sCurve->project->project_name ?? 'N/A') }}</h3>
                <strong>Period:</strong> {{ \Carbon\Carbon::parse($sCurve->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($sCurve->end_date)->format('d M Y') }} ({{ $totalWeeks }} Weeks)
            </td>
            <td style="width: 30%; text-align: right; vertical-align: top;">
                @if($lastInputWeek > 0)
                <div class="status-box" style="text-align: left; display: inline-block;">
                    <strong>CURRENT WEEK: {{ $lastInputWeek }}</strong><br>
                    PLAN: {{ number_format($plannedProgress, 2) }}%<br>
                    ACTUAL: {{ number_format($actualProgress, 2) }}%<br>
                    DEV: {{ $currentDeviation > 0 ? '+' : '' }}{{ number_format($currentDeviation, 2) }}%<br>
                    STATUS: <strong>{{ $projectStatus }}</strong>
                </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="grid-container">
        <!-- HEADER TABLE -->
        <table style="margin-bottom: 0; border-bottom: none;">
            <colgroup>
                <col style="width: 3%;">
                <col style="width: 22%;">
                <col style="width: 5%;">
                @for($w = 1; $w <= $totalWeeks; $w++)
                    <col style="width: {{ 70 / $totalWeeks }}%;">
                @endfor
            </colgroup>
        
        <table>
            <thead>
                <tr>
                    <th rowspan="2">NO</th>
                    <th rowspan="2">JENIS PEKERJAAN</th>
                    <th rowspan="2">BOBOT</th>
                    <th colspan="{{ $totalWeeks }}">WEEK</th>
                </tr>
                <tr>
                    @for($w = 1; $w <= $totalWeeks; $w++)
                        <th class="{{ $w == $lastInputWeek ? 'bg-highlight' : '' }}">{{ $w }}</th>
                    @endfor
                </tr>
            </thead>
        </table>
        
        <!-- WBS TABLE -->
        <table style="margin-bottom: 0; border-top: none; border-bottom: none;">
                <colgroup>
                    <col style="width: 3%;">
                    <col style="width: 22%;">
                    <col style="width: 5%;">
                    @for($w = 1; $w <= $totalWeeks; $w++)
                        <col style="width: {{ 70 / $totalWeeks }}%;">
                    @endfor
                </colgroup>
                <tbody>
                @php
                    function getWbsNumber($index, $level) {
                        if ($level == 0) {
                            $map = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV'];
                            return $map[$index] ?? ($index + 1);
                        } elseif ($level == 1) {
                            return chr(97 + $index) . '.'; // a., b., c.
                        } elseif ($level == 2) {
                            return ($index + 1) . '.';
                        } else {
                            return chr(97 + $index) . '.';
                        }
                    }

                    function renderPdfTreeRows($nodes, $level, $totalWeeks, &$globalCounters, $lastInputWeek) {
                        $html = '';
                        if (!isset($globalCounters[$level])) {
                            $globalCounters[$level] = 0;
                        }
                        if (!isset($globalCounters['rowCount'])) {
                            $globalCounters['rowCount'] = 0;
                        }
                        
                        foreach ($nodes as $node) {
                            $isParent = count($node->children_nodes) > 0;
                            $padding = $level * 10;
                            $number = getWbsNumber($globalCounters[$level], $level);
                            
                            $html .= '<tr class="wbs-row">';
                            $globalCounters['rowCount']++;
                            
                            $html .= '<td class="text-center" style="font-weight: '.($isParent ? 'bold' : 'normal').'">'.$number.'</td>';
                            $html .= '<td class="text-left" style="padding-left: '.$padding.'px; font-weight: '.($isParent ? 'bold' : 'normal').'">'.e($node->work_name).'</td>';
                            $html .= '<td style="font-weight: '.($isParent ? 'bold' : 'normal').'">'.number_format($node->weight_percentage, 2).'%</td>';
                            
                            if ($isParent) {
                                for ($w = 1; $w <= $totalWeeks; $w++) {
                                    $bg = ($w == $lastInputWeek) ? 'background-color: #ffffdd;' : '';
                                    $html .= '<td style="'.$bg.'"></td>';
                                }
                            } else {
                                $plans = [];
                                foreach ($node->plans as $p) $plans[$p->week_number] = $p->planned_percentage;
                                
                                $actuals = [];
                                foreach ($node->actuals as $a) $actuals[$a->week_number] = $a->actual_percentage;
                                
                                for ($w = 1; $w <= $totalWeeks; $w++) {
                                    $pVal = $plans[$w] ?? 0;
                                    $aVal = $actuals[$w] ?? 0;
                                    $bg = ($w == $lastInputWeek) ? 'background-color: #ffffdd;' : '';
                                    
                                    $html .= '<td style="'.$bg.'">';
                                    if ($pVal > 0) $html .= '<span class="plan-val">'.number_format($pVal, 2).'</span>';
                                    else $html .= '<span class="plan-val">&nbsp;</span>';
                                    
                                    if ($aVal > 0) $html .= '<span class="act-val">'.number_format($aVal, 2).'</span>';
                                    else $html .= '<span class="act-val">&nbsp;</span>';
                                    $html .= '</td>';
                                }
                            }
                            $html .= '</tr>';
                            
                            $globalCounters[$level]++;
                            
                            if ($isParent) {
                                $globalCounters[$level + 1] = 0; // reset child counter
                                $html .= renderPdfTreeRows($node->children_nodes, $level + 1, $totalWeeks, $globalCounters, $lastInputWeek);
                            }
                        }
                        return $html;
                    }
                    
                    $counters = ['rowCount' => 0];
                    $treeHtml = renderPdfTreeRows($tree, 0, $totalWeeks, $counters, $lastInputWeek);
                    $totalRows = $counters['rowCount'];
                    
                    // Calculate exact physical dimensions for DOMPDF
                    $rowHeightPt = 20;
                    $svgHeightPt = $totalRows * $rowHeightPt;
                    
                    // A4 landscape = 842pt width. Margins 5mm (14.17pt) each side. 
                    // Content width = 842 - 28.34 = 813.66pt
                    // Week grid is 70% of table width.
                    // 0.70 * 813.66 = 569.56pt
                    $svgWidthPt = 569.56;
                @endphp
                
                {!! $treeHtml !!}
            </tbody>
        </table>
        
        <!-- SVG OVERLAY (Rendered as Base64 Image for DOMPDF compatibility) -->
        @php
            $planCircles = [];
            $actualCircles = [];
            
            $divisor = max(1, $totalWeeks - 1);
            
            for ($w = 1; $w <= $totalWeeks; $w++) {
                // User's exact formula: x = chartAreaLeft + ((weekNumber - 1) / (totalWeeks - 1)) * chartAreaWidth
                $xPt = (($w - 1) / $divisor) * $svgWidthPt;
                
                $pVal = $cumPlans[$w] ?? 0;
                $yPlanPt = $svgHeightPt - (($pVal / 100) * $svgHeightPt);
                $planCircles[] = ['x' => $xPt, 'y' => $yPlanPt];
                
                if ($w <= $lastInputWeek) {
                    $aVal = $cumActuals[$w] ?? 0;
                    $yActualPt = $svgHeightPt - (($aVal / 100) * $svgHeightPt);
                    $actualCircles[] = ['x' => $xPt, 'y' => $yActualPt];
                }
            }
            
            $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$svgWidthPt.'pt" height="'.$svgHeightPt.'pt" viewBox="0 0 '.$svgWidthPt.' '.$svgHeightPt.'">';
            
            // Plan Curve
            for ($i = 0; $i < count($planCircles) - 1; $i++) {
                $svgContent .= '<line x1="'.$planCircles[$i]['x'].'" y1="'.$planCircles[$i]['y'].'" x2="'.$planCircles[$i+1]['x'].'" y2="'.$planCircles[$i+1]['y'].'" stroke="#0044cc" stroke-width="1.5" />';
            }
            
            // Actual Curve
            for ($i = 0; $i < count($actualCircles) - 1; $i++) {
                $svgContent .= '<line x1="'.$actualCircles[$i]['x'].'" y1="'.$actualCircles[$i]['y'].'" x2="'.$actualCircles[$i+1]['x'].'" y2="'.$actualCircles[$i+1]['y'].'" stroke="#cc0000" stroke-width="1.5" />';
            }
            
            // Plan Markers
            foreach ($planCircles as $pt) {
                $svgContent .= '<circle cx="'.$pt['x'].'" cy="'.$pt['y'].'" r="2" fill="#0044cc" />';
            }
            
            // Actual Markers
            foreach ($actualCircles as $pt) {
                $svgContent .= '<circle cx="'.$pt['x'].'" cy="'.$pt['y'].'" r="2" fill="#cc0000" />';
            }
            
            $svgContent .= '</svg>';
            $base64Svg = 'data:image/svg+xml;base64,' . base64_encode($svgContent);
        @endphp
        
        <div style="margin-top: -{{ $svgHeightPt }}pt; margin-left: 244.10pt; width: {{ $svgWidthPt }}pt; height: {{ $svgHeightPt }}pt; z-index: 100;">
            <img src="{{ $base64Svg }}" style="width: 100%; height: 100%;">
        </div>
        
        <!-- FOOTER TABLE -->
        <table style="border-top: none;">
            <colgroup>
                <col style="width: 3%;">
                <col style="width: 22%;">
                <col style="width: 5%;">
                @for($w = 1; $w <= $totalWeeks; $w++)
                    <col style="width: {{ 70 / $totalWeeks }}%;">
                @endfor
            </colgroup>
            <tbody>
                <!-- Cumulative Plan Row -->
                <tr>
                    <td colspan="3" class="text-right font-weight-bold" style="font-weight: bold; color: #0044cc; background-color: #f8f9fa;">PLAN CUMULATIVE (%)</td>
                    @for($w = 1; $w <= $totalWeeks; $w++)
                        <td style="font-weight: bold; color: #0044cc; font-size: 9px; background-color: #f8f9fa;">{{ number_format($cumPlans[$w] ?? 0, 2) }}</td>
                    @endfor
                </tr>
                <!-- Cumulative Actual Row -->
                <tr>
                    <td colspan="3" class="text-right font-weight-bold" style="font-weight: bold; color: #cc0000; background-color: #f8f9fa;">ACTUAL CUMULATIVE (%)</td>
                    @for($w = 1; $w <= $totalWeeks; $w++)
                        <td style="font-weight: bold; color: #cc0000; font-size: 9px; background-color: #f8f9fa;">
                            @if($w <= $lastInputWeek)
                                {{ number_format($cumActuals[$w] ?? 0, 2) }}
                            @endif
                        </td>
                    @endfor
                </tr>
                <!-- Deviation Row -->
                <tr>
                    <td colspan="3" class="text-right font-weight-bold" style="font-weight: bold; background-color: #f8f9fa;">DEVIATION (%)</td>
                    @for($w = 1; $w <= $totalWeeks; $w++)
                        @php
                            $dev = ($cumActuals[$w] ?? 0) - ($cumPlans[$w] ?? 0);
                            $color = $dev >= 0 ? 'green' : 'red';
                        @endphp
                        <td style="font-weight: bold; color: {{ $color }}; font-size: 9px; background-color: #f8f9fa;">
                            @if($w <= $lastInputWeek)
                                {{ $dev > 0 ? '+' : '' }}{{ number_format($dev, 2) }}
                            @endif
                        </td>
                    @endfor
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
