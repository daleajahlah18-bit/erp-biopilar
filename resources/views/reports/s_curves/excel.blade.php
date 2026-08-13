<table>
    <thead>
        <tr>
            <th colspan="{{ $totalWeeks + 2 }}" style="text-align: center; font-weight: bold; font-size: 14pt;">S-CURVE PROGRESS REPORT</th>
        </tr>
        <tr>
            <th colspan="{{ $totalWeeks + 2 }}" style="text-align: center; font-weight: bold;">{{ $sCurve->project->project_name ?? '-' }}</th>
        </tr>
        <tr>
            <th colspan="{{ $totalWeeks + 2 }}"></th>
        </tr>
        <tr>
            <th>S-Curve Name:</th>
            <th>{{ $sCurve->name }}</th>
            <th colspan="{{ $totalWeeks - 2 }}"></th>
            <th>Duration:</th>
            <th>{{ \Carbon\Carbon::parse($sCurve->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($sCurve->end_date)->format('d M Y') }} ({{ $totalWeeks }} Weeks)</th>
        </tr>
        <tr>
            <th>Client:</th>
            <th>{{ $sCurve->project->client_name ?? '-' }}</th>
            <th colspan="{{ $totalWeeks - 2 }}"></th>
            <th>Created Date:</th>
            <th>{{ $sCurve->created_at->format('d M Y') }}</th>
        </tr>
        <tr>
            <th colspan="{{ $totalWeeks + 2 }}"></th>
        </tr>
        
        <tr>
            <th rowspan="2" style="font-weight: bold; text-align: center; border: 1px solid #000;">WBS</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; border: 1px solid #000;">Weight (%)</th>
            <th colspan="{{ $totalWeeks }}" style="font-weight: bold; text-align: center; border: 1px solid #000;">Week</th>
        </tr>
        <tr>
            @for($w = 1; $w <= $totalWeeks; $w++)
                <th style="font-weight: bold; text-align: center; border: 1px solid #000;">{{ $w }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @php
            function renderExcelTree($nodes, $level, $totalWeeks) {
                $html = '';
                foreach ($nodes as $node) {
                    $isParent = count($node->children_nodes) > 0;
                    $indent = str_repeat('    ', $level);
                    
                    $html .= '<tr>';
                    $html .= '<td style="border: 1px solid #000;">';
                    $html .= $isParent ? '<strong>'.$indent.e($node->work_name).'</strong>' : $indent.e($node->work_name);
                    $html .= '</td>';
                    $html .= '<td style="border: 1px solid #000;">'.($isParent ? '<strong>'.number_format($node->weight_percentage, 2).'</strong>' : number_format($node->weight_percentage, 2)).'</td>';
                    
                    $plans = [];
                    foreach ($node->plans as $p) $plans[$p->week_number] = $p->planned_percentage;
                    
                    for ($w = 1; $w <= $totalWeeks; $w++) {
                        $val = $plans[$w] ?? 0;
                        $html .= '<td style="border: 1px solid #000;">'.($val > 0 ? number_format($val, 2) : '-').'</td>';
                    }
                    $html .= '</tr>';
                    
                    if ($isParent) {
                        $html .= renderExcelTree($node->children_nodes, $level + 1, $totalWeeks);
                    }
                }
                return $html;
            }
        @endphp
        
        {!! renderExcelTree($tree, 0, $totalWeeks) !!}
    </tbody>
</table>
