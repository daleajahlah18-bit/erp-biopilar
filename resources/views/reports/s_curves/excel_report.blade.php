<table>
    <thead>
        <tr>
            <th colspan="{{ $totalWeeks + 1 }}" style="text-align: center; font-weight: bold; font-size: 16pt;">S-CURVE PROJECT PROGRESS REPORT</th>
        </tr>
        <tr>
            <th colspan="{{ $totalWeeks + 1 }}" style="text-align: center; font-weight: bold; font-size: 14pt;">{{ $sCurve->project->project_name ?? '-' }}</th>
        </tr>
        <tr>
            <th colspan="{{ $totalWeeks + 1 }}"></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-weight: bold;">S-Curve Name</td>
            <td>: {{ $sCurve->name }}</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Client</td>
            <td>: {{ $sCurve->project->client_name ?? '-' }}</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Project Period</td>
            <td>: {{ \Carbon\Carbon::parse($sCurve->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($sCurve->end_date)->format('d M Y') }}</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total Duration</td>
            <td>: {{ $totalWeeks }} Weeks</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Current Week</td>
            <td>: W{{ $lastInputWeek }}</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Created Date</td>
            <td>: {{ $sCurve->created_at->format('d M Y') }}</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        
        <tr><td colspan="{{ $totalWeeks + 1 }}"></td></tr>
        
        <!-- SUMMARY SECTION -->
        <tr>
            <td style="font-weight: bold; background-color: #f0f0f0;">SUMMARY STATUS</td>
            <td style="background-color: #f0f0f0;"></td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">PLAN CUMULATIVE</td>
            <td style="color: #0044cc; font-weight: bold;">: {{ number_format($plannedProgress, 2) }}%</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">ACTUAL CUMULATIVE</td>
            <td style="color: #cc0000; font-weight: bold;">: {{ number_format($actualProgress, 2) }}%</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">DEVIATION</td>
            <td style="font-weight: bold; color: {{ $currentDeviation < 0 ? '#cc0000' : '#008800' }};">: {{ number_format($currentDeviation, 2) }}%</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">PROJECT STATUS</td>
            <td style="font-weight: bold;">: {{ $projectStatus }}</td>
            <td colspan="{{ $totalWeeks - 1 }}"></td>
        </tr>
        
        <tr>
            <td colspan="{{ $totalWeeks + 1 }}"></td>
        </tr>
        <tr>
            <td colspan="{{ $totalWeeks + 1 }}" style="font-weight: bold; background-color: #fff2cc; border: 1px solid #d6b656; text-align: center;">
                S-CURVE DATA SOURCE:<br>
                Native Chart removed to prevent Excel XML corruption. <br>
                Please use the data on the <strong>"Weekly Progress"</strong> sheet to manually insert a Line Chart for Plan Cumulative vs Actual Cumulative.
            </td>
        </tr>
        <tr>
            <td colspan="{{ $totalWeeks + 1 }}"></td>
        </tr>
        
        <!-- WEEKLY SUMMARY TABLE -->
        <tr>
            <td style="font-weight: bold; text-align: center; border: 1px solid #000; background-color: #e2efd9;">Metric</td>
            @for($w = 1; $w <= $totalWeeks; $w++)
                <td style="font-weight: bold; text-align: center; border: 1px solid #000; background-color: #e2efd9;">W{{ $w }}</td>
            @endfor
        </tr>
        
        <tr>
            <td style="font-weight: bold; border: 1px solid #000;">Plan Weekly (%)</td>
            @for($w = 1; $w <= $totalWeeks; $w++)
                <td style="text-align: right; border: 1px solid #000;">{{ number_format($weeklyPlans[$w] ?? 0, 2) }}</td>
            @endfor
        </tr>
        
        <tr>
            <td style="font-weight: bold; border: 1px solid #000;">Actual Weekly (%)</td>
            @for($w = 1; $w <= $totalWeeks; $w++)
                <td style="text-align: right; border: 1px solid #000;">{{ number_format($weeklyActuals[$w] ?? 0, 2) }}</td>
            @endfor
        </tr>
        
        <tr>
            <td style="font-weight: bold; border: 1px solid #000;">Plan Cumulative (%)</td>
            @for($w = 1; $w <= $totalWeeks; $w++)
                <td style="text-align: right; border: 1px solid #000; color: #0044cc; font-weight: bold;">{{ number_format($cumPlans[$w] ?? 0, 2) }}</td>
            @endfor
        </tr>
        
        <tr>
            <td style="font-weight: bold; border: 1px solid #000;">Actual Cumulative (%)</td>
            @for($w = 1; $w <= $totalWeeks; $w++)
                <td style="text-align: right; border: 1px solid #000; color: #cc0000; font-weight: bold;">{{ number_format($cumActuals[$w] ?? 0, 2) }}</td>
            @endfor
        </tr>
        
        <tr>
            <td style="font-weight: bold; border: 1px solid #000;">Deviation (%)</td>
            @for($w = 1; $w <= $totalWeeks; $w++)
                @php $dev = $deviations[$w] ?? 0; @endphp
                <td style="text-align: right; border: 1px solid #000; font-weight: bold; color: {{ $dev < 0 ? '#cc0000' : '#008800' }};">{{ number_format($dev, 2) }}</td>
            @endfor
        </tr>
    </tbody>
</table>
