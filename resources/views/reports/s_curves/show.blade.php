@extends('layouts.app')
@section('title', 'S-Curve Dashboard')
@section('page_title', 'Dashboard: ' . $sCurve->name)
@section('header_actions')
<a href="{{ route('s-curves.index') }}" class="btn-outline-secondary btn"><i class="bi bi-arrow-left"></i> Kembali</a>
<form action="{{ route('s-curves.pdf', $sCurve) }}" method="POST" id="pdfExportForm" class="d-inline ms-2">
    @csrf
    <input type="hidden" name="chart_image" id="chart_image_input">
    <button type="button" class="btn-outline-danger btn" id="exportPdfBtn"><i class="bi bi-file-pdf"></i> Export PDF</button>
</form>
<a href="{{ route('s-curves.excel', $sCurve) }}" class="btn-success btn ms-2"><i class="bi bi-file-excel"></i> Export Excel</a>
@endsection

@section('content')
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Durasi Project</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalWeeks }} Minggu</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-calendar fs-2 text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Planned Progress</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($plannedProgress, 2) }}%</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-bullseye fs-2 text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Actual Progress</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($actualProgress, 2) }}%</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-graph-up fs-2 text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        @php
            $statusColor = 'success';
            if ($projectStatus == 'BEHIND SCHEDULE') $statusColor = 'danger';
            if ($projectStatus == 'AHEAD') $statusColor = 'primary';
        @endphp
        <div class="card shadow-sm border-left-{{ $statusColor }} h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-{{ $statusColor }} text-uppercase mb-1">Status (Deviasi: {{ number_format($currentDeviation, 2) }}%)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $projectStatus }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-speedometer2 fs-2 text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Grafik S-Curve</h6>
    </div>
    <div class="card-body">
        <canvas id="sCurveChart" style="height: 400px;"></canvas>
    </div>
</div>

<!-- WBS Data & Weekly Inputs -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Work Breakdown Structure (WBS) & Progress</h6>
        <div>
            <button class="btn btn-sm btn-primary me-2" onclick="openWbsModal()"><i class="bi bi-list-task"></i> Edit WBS</button>
            <button class="btn btn-sm btn-info me-2 text-white" onclick="openPlanModal()"><i class="bi bi-calendar-check"></i> Edit Plan</button>
            <button class="btn btn-sm btn-success" onclick="openActualModal()"><i class="bi bi-graph-up"></i> Edit Actual</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 text-center" style="font-size: 0.85rem;">
                <thead class="bg-light align-middle">
                    <tr>
                        <th rowspan="2">WBS</th>
                        <th rowspan="2">Bobot (%)</th>
                        <th colspan="{{ $totalWeeks }}">Minggu Ke-</th>
                    </tr>
                    <tr>
                        @for($w = 1; $w <= $totalWeeks; $w++)
                            <th style="min-width: 50px;">{{ $w }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        function renderHtmlTree($nodes, $level, $totalWeeks, $weeklyPlans, $weeklyActuals) {
                            $html = '';
                            foreach ($nodes as $node) {
                                $isParent = count($node->children_nodes) > 0;
                                $padding = $level * 20;
                                
                                $html .= '<tr>';
                                $html .= '<td class="text-start" style="padding-left: '.$padding.'px;">';
                                $html .= $isParent ? '<strong>'.$node->work_name.'</strong>' : $node->work_name;
                                $html .= '</td>';
                                $html .= '<td>'.($isParent ? '<strong>'.number_format($node->weight_percentage, 2).'</strong>' : number_format($node->weight_percentage, 2)).'</td>';
                                
                                $plans = [];
                                foreach ($node->plans as $p) $plans[$p->week_number] = $p->planned_percentage;
                                
                                $actuals = [];
                                foreach ($node->actuals as $a) $actuals[$a->week_number] = $a->actual_percentage;
                                
                                for ($w = 1; $w <= $totalWeeks; $w++) {
                                    $valPlan = $plans[$w] ?? 0;
                                    $valActual = $actuals[$w] ?? 0;
                                    
                                    $tdContent = '-';
                                    if ($valPlan > 0 || $valActual > 0) {
                                        $tdContent = '<div class="small text-primary">P: '.($valPlan > 0 ? number_format($valPlan, 2) : '-').'</div>';
                                        $tdContent .= '<div class="small text-success">A: '.($valActual > 0 ? number_format($valActual, 2) : '-').'</div>';
                                    }
                                    
                                    $html .= '<td>'.$tdContent.'</td>';
                                }
                                $html .= '</tr>';
                                
                                if ($isParent) {
                                    $html .= renderHtmlTree($node->children_nodes, $level + 1, $totalWeeks, $weeklyPlans, $weeklyActuals);
                                }
                            }
                            return $html;
                        }
                    @endphp
                    {!! renderHtmlTree($tree, 0, $totalWeeks, $weeklyPlans, $weeklyActuals) !!}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- S-Curve Data Source -->
<div class="card shadow-sm mb-4">
    <div class="card-header py-3" style="background-color: #fff3cd; border-bottom: 1px solid #ffe69c;">
        <h5 class="m-0 font-weight-bold text-center text-dark">S-CURVE DATA SOURCE:</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 text-center text-nowrap" style="font-size: 0.85rem;">
                <thead class="bg-light align-middle">
                    <tr>
                        <th class="text-start" style="min-width: 200px;">Metric</th>
                        @for($w = 1; $w <= $totalWeeks; $w++)
                            <th style="min-width: 60px;">W{{ $w }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    <!-- Plan Weekly -->
                    <tr>
                        <td class="text-start fw-bold">Plan Weekly (%)</td>
                        @for($w = 1; $w <= $totalWeeks; $w++)
                            <td class="text-primary">{{ number_format($weeklyPlans[$w] ?? 0, 2) }}</td>
                        @endfor
                    </tr>
                    <!-- Actual Weekly -->
                    <tr>
                        <td class="text-start fw-bold">Actual Weekly (%)</td>
                        @for($w = 1; $w <= $totalWeeks; $w++)
                            <td class="text-danger">{{ number_format($weeklyActuals[$w] ?? 0, 2) }}</td>
                        @endfor
                    </tr>
                    <!-- Plan Cumulative -->
                    <tr>
                        <td class="text-start fw-bold">Plan Cumulative (%)</td>
                        @for($w = 1; $w <= $totalWeeks; $w++)
                            <td class="text-primary">{{ number_format($cumPlans[$w] ?? 0, 2) }}</td>
                        @endfor
                    </tr>
                    <!-- Actual Cumulative -->
                    <tr>
                        <td class="text-start fw-bold">Actual Cumulative (%)</td>
                        @for($w = 1; $w <= $totalWeeks; $w++)
                            <td class="text-danger">{{ number_format($cumActuals[$w] ?? 0, 2) }}</td>
                        @endfor
                    </tr>
                    <!-- Deviation -->
                    <tr>
                        <td class="text-start fw-bold">Deviation (%)</td>
                        @for($w = 1; $w <= $totalWeeks; $w++)
                            <td class="text-danger">{{ number_format($differences[$w] ?? 0, 2) }}</td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- WBS Modal -->
<div class="modal fade" id="wbsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">WBS Builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="mb-3">
                    <button class="btn btn-sm btn-primary" onclick="addWbsNode(null)"><i class="bi bi-plus"></i> Tambah Parent Baru</button>
                    <span class="ms-3 text-muted small">Total Leaf Weight: <strong id="totalLeafWeightLabel">0</strong>% (Harus 100%)</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered bg-white table-sm" id="wbsBuilderTable">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th>Work Name</th>
                                <th width="150">Weight (%)</th>
                                <th width="200" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wbsBuilderBody">
                            <!-- JS Generated -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveWbs()"><i class="bi bi-save"></i> Simpan WBS</button>
            </div>
        </div>
    </div>
</div>

<!-- CSS for Sticky Table -->
<style>
    .table-sticky-wrapper {
        overflow: auto;
        max-height: 65vh;
        background-color: #fff;
    }
    .table-sticky {
        border-collapse: separate; 
        border-spacing: 0;
        margin-bottom: 0;
    }
    .table-sticky th, .table-sticky td {
        border-bottom: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        vertical-align: middle;
        white-space: nowrap;
    }
    .table-sticky thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    
    /* Sticky Columns */
    .col-wbs { position: sticky; left: 0; z-index: 8; background-color: #f8f9fa; min-width: 220px; max-width: 220px; width: 220px; border-left: 1px solid #dee2e6; text-align: left !important; overflow: hidden; text-overflow: ellipsis; }
    .col-weight { position: sticky; left: 220px; z-index: 8; background-color: #f8f9fa; min-width: 80px; max-width: 80px; width: 80px; }
    .col-total { position: sticky; left: 300px; z-index: 8; background-color: #f8f9fa; min-width: 100px; max-width: 100px; width: 100px; }
    
    /* Body sticky elements need lower z-index than headers */
    .table-sticky tbody .col-wbs, 
    .table-sticky tbody .col-weight, 
    .table-sticky tbody .col-total {
        z-index: 5;
        background-color: #ffffff;
    }
    
    /* Week columns */
    .col-week {
        min-width: 90px;
        width: 90px;
        text-align: center;
    }
    
    /* Input field styling */
    .plan-input, .actual-input {
        width: 100%;
        height: 44px;
        text-align: center;
        font-size: 15px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .plan-input:focus, .actual-input:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        background-color: #eaf2ff;
    }
    
    /* Hover effect for row */
    .table-sticky tbody tr:hover td {
        background-color: #f8f9fa;
    }
    .table-sticky tbody tr:hover .col-wbs,
    .table-sticky tbody tr:hover .col-weight,
    .table-sticky tbody tr:hover .col-total {
        background-color: #f8f9fa;
    }
</style>

<!-- Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Weekly Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-sticky-wrapper">
                    <table class="table table-sticky text-center" id="planTable">
                        <thead>
                            <tr>
                                <th class="col-wbs">Leaf WBS</th>
                                <th class="col-weight">Bobot (%)</th>
                                <th class="col-total">Total Plan</th>
                                @for($w = 1; $w <= $totalWeeks; $w++)
                                    <th class="col-week">W{{ $w }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                function renderPlanTree($nodes, $level, $totalWeeks) {
                                    $html = '';
                                    foreach ($nodes as $node) {
                                        $isParent = count($node->children_nodes) > 0;
                                        $padding = $level * 20;
                                        
                                        $html .= '<tr' . (!$isParent ? ' data-item-id="'.$node->id.'" data-weight="'.$node->weight_percentage.'"' : '') . '>';
                                        
                                        $html .= '<td class="col-wbs text-start" style="padding-left: '.($padding + 10).'px;" title="'.$node->work_name.'">';
                                        $html .= $isParent ? '<strong>'.$node->work_name.'</strong>' : $node->work_name;
                                        $html .= '</td>';
                                        
                                        $html .= '<td class="col-weight">';
                                        $html .= $isParent ? '<strong>'.number_format($node->weight_percentage, 2).'</strong>' : number_format($node->weight_percentage, 2);
                                        $html .= '</td>';
                                        
                                        $html .= '<td class="col-total fw-bold '.(!$isParent ? 'plan-total-col' : '').'">'.($isParent ? '' : '0.00').'</td>';
                                        
                                        if ($isParent) {
                                            for ($w = 1; $w <= $totalWeeks; $w++) {
                                                $html .= '<td class="bg-light"></td>';
                                            }
                                        } else {
                                            $leafPlans = [];
                                            foreach($node->plans as $p) $leafPlans[$p->week_number] = $p->planned_percentage;
                                            
                                            for ($w = 1; $w <= $totalWeeks; $w++) {
                                                $val = $leafPlans[$w] ?? '';
                                                $html .= '<td>';
                                                $html .= '<input type="number" step="0.01" min="0" class="plan-input" data-week="'.$w.'" value="'.$val.'">';
                                                $html .= '</td>';
                                            }
                                        }
                                        
                                        $html .= '</tr>';
                                        
                                        if ($isParent) {
                                            $html .= renderPlanTree($node->children_nodes, $level + 1, $totalWeeks);
                                        }
                                    }
                                    return $html;
                                }
                            @endphp
                            {!! renderPlanTree($tree, 0, $totalWeeks) !!}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="savePlans()"><i class="bi bi-save"></i> Simpan Plan</button>
            </div>
        </div>
    </div>
</div>

<!-- Actual Modal -->
<div class="modal fade" id="actualModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Weekly Actual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-sticky-wrapper">
                    <table class="table table-sticky text-center" id="actualTable">
                        <thead>
                            <tr>
                                <th class="col-wbs">Leaf WBS</th>
                                <th class="col-weight">Bobot (%)</th>
                                <th class="col-total">Total Actual</th>
                                @for($w = 1; $w <= $totalWeeks; $w++)
                                    <th class="col-week">W{{ $w }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                function renderActualTree($nodes, $level, $totalWeeks) {
                                    $html = '';
                                    foreach ($nodes as $node) {
                                        $isParent = count($node->children_nodes) > 0;
                                        $padding = $level * 20;
                                        
                                        $html .= '<tr' . (!$isParent ? ' data-item-id="'.$node->id.'" data-weight="'.$node->weight_percentage.'"' : '') . '>';
                                        
                                        $html .= '<td class="col-wbs text-start" style="padding-left: '.($padding + 10).'px;" title="'.$node->work_name.'">';
                                        $html .= $isParent ? '<strong>'.$node->work_name.'</strong>' : $node->work_name;
                                        $html .= '</td>';
                                        
                                        $html .= '<td class="col-weight">';
                                        $html .= $isParent ? '<strong>'.number_format($node->weight_percentage, 2).'</strong>' : number_format($node->weight_percentage, 2);
                                        $html .= '</td>';
                                        
                                        $html .= '<td class="col-total fw-bold '.(!$isParent ? 'actual-total-col' : '').'">'.($isParent ? '' : '0.00').'</td>';
                                        
                                        if ($isParent) {
                                            for ($w = 1; $w <= $totalWeeks; $w++) {
                                                $html .= '<td class="bg-light"></td>';
                                            }
                                        } else {
                                            $leafActuals = [];
                                            foreach($node->actuals as $a) $leafActuals[$a->week_number] = $a->actual_percentage;
                                            
                                            for ($w = 1; $w <= $totalWeeks; $w++) {
                                                $val = $leafActuals[$w] ?? '';
                                                $html .= '<td>';
                                                $html .= '<input type="number" step="0.01" min="0" class="actual-input" data-week="'.$w.'" value="'.$val.'">';
                                                $html .= '</td>';
                                            }
                                        }
                                        
                                        $html .= '</tr>';
                                        
                                        if ($isParent) {
                                            $html .= renderActualTree($node->children_nodes, $level + 1, $totalWeeks);
                                        }
                                    }
                                    return $html;
                                }
                            @endphp
                            {!! renderActualTree($tree, 0, $totalWeeks) !!}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveActuals()"><i class="bi bi-save"></i> Simpan Actual</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    console.log('[SCURVE PDF] JAVASCRIPT FILE LOADED');

    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('exportPdfBtn');
        console.log('[SCURVE PDF] Looking for export button...', btn);
        if (!btn) {
            console.error('[SCURVE PDF] EXPORT BUTTON NOT FOUND');
        } else {
            console.log('[SCURVE PDF] EXPORT BUTTON FOUND');
            btn.addEventListener('click', function (event) {
                console.log('[SCURVE PDF] BUTTON CLICKED');
                exportPdf();
            });
        }
    });

    // --- CHART JS ---
    const ctx = document.getElementById('sCurveChart').getContext('2d');
    const labels = [];
    @for($w = 1; $w <= $totalWeeks; $w++)
        labels.push('W{{ $w }}');
    @endfor

    const plannedData = {!! json_encode(array_values($cumPlans)) !!};
    const actualData = {!! json_encode(array_values($cumActuals)) !!};

    window.sCurveChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Plan Cumulative (%)',
                    data: plannedData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Actual Cumulative (%)',
                    data: actualData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Progress (%)' }
                },
                x: {
                    title: { display: true, text: 'Minggu (Weeks)' }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
            }
        }
    });

    function exportPdf() {
        console.log('[SCURVE PDF] 1. FUNCTION START');
        
        const chart = window.sCurveChartInstance;
        
        if (!chart) {
            console.error('[SCURVE PDF] 2. Chart instance NOT FOUND');
            alert('Chart S-Curve belum tersedia.');
            return;
        }
        
        console.log('[SCURVE PDF] 2. Chart instance OK');
        
        const form = document.getElementById('pdfExportForm');
        
        if (!form) {
            console.error('[SCURVE PDF] 3. PDF FORM NOT FOUND');
            alert('Form Export PDF tidak ditemukan.');
            return;
        }
        
        console.log('[SCURVE PDF] 3. PDF FORM FOUND');
        
        // Save original config safely
        const originalXDisplay = chart.options.scales.x.display;
        const originalYDisplay = chart.options.scales.y.display;
        const originalXGridDisplay = chart.options.scales.x.grid ? chart.options.scales.x.grid.display : undefined;
        const originalYGridDisplay = chart.options.scales.y.grid ? chart.options.scales.y.grid.display : undefined;
        
        const hasLegend = chart.options.plugins && chart.options.plugins.legend;
        const originalLegendDisplay = hasLegend ? chart.options.plugins.legend.display : undefined;
        
        // Hide axes and legend for PDF overlay
        chart.options.scales.x.display = false;
        chart.options.scales.y.display = false;
        
        if (!chart.options.scales.x.grid) chart.options.scales.x.grid = {};
        if (!chart.options.scales.y.grid) chart.options.scales.y.grid = {};
        chart.options.scales.x.grid.display = false;
        chart.options.scales.y.grid.display = false;
        
        if (hasLegend) {
            chart.options.plugins.legend.display = false;
        }
        
        // Update chart synchronously
        chart.update('none');
        
        const canvas = document.getElementById('sCurveChart');
        const imgData = canvas.toDataURL('image/png');
        document.getElementById('chart_image_input').value = imgData;
        
        // Revert config
        chart.options.scales.x.display = originalXDisplay;
        chart.options.scales.y.display = originalYDisplay;
        
        if (originalXGridDisplay !== undefined) chart.options.scales.x.grid.display = originalXGridDisplay;
        if (originalYGridDisplay !== undefined) chart.options.scales.y.grid.display = originalYGridDisplay;
        
        if (hasLegend && originalLegendDisplay !== undefined) {
            chart.options.plugins.legend.display = originalLegendDisplay;
        }
        
        chart.update('none');
        
        document.getElementById('pdfExportForm').submit();
    }


    // --- WBS BUILDER ---
    let wbsNodes = {!! json_encode($items) !!};
    let wbsCounter = 0;

    function openWbsModal() {
        renderWbsTable();
        new bootstrap.Modal(document.getElementById('wbsModal')).show();
    }

    function generateId() {
        wbsCounter++;
        return 'new_' + wbsCounter;
    }

    function addWbsNode(parentId) {
        wbsNodes.push({
            id: generateId(),
            parent_id: parentId,
            work_name: '',
            weight_percentage: 0,
            sort_order: wbsNodes.filter(n => n.parent_id == parentId).length + 1
        });
        renderWbsTable();
    }

    function deleteWbsNode(id) {
        // Find all children recursively to delete
        let idsToDelete = [String(id)];
        let found = true;
        while(found) {
            found = false;
            wbsNodes.forEach(n => {
                if(idsToDelete.includes(String(n.parent_id)) && !idsToDelete.includes(String(n.id))) {
                    idsToDelete.push(String(n.id));
                    found = true;
                }
            });
        }
        wbsNodes = wbsNodes.filter(n => !idsToDelete.includes(String(n.id)));
        renderWbsTable();
    }

    function updateNodeData(id, field, value) {
        const node = wbsNodes.find(n => String(n.id) === String(id));
        if (node) {
            node[field] = value;
            if (field === 'weight_percentage') {
                calculateParentWeights();
            }
        }
    }

    function calculateParentWeights() {
        let hasChanges = false;
        
        // Find maximum depth to process bottom-up
        let maxDepth = 0;
        wbsNodes.forEach(n => {
            let depth = 0;
            let current = n;
            while(current.parent_id) {
                depth++;
                current = wbsNodes.find(p => String(p.id) === String(current.parent_id));
                if(!current) break;
            }
            if(depth > maxDepth) maxDepth = depth;
        });

        // Bottom up calculation
        for (let d = maxDepth; d >= 0; d--) {
            wbsNodes.forEach(parent => {
                // Check if it's a parent at this depth (simplified, just check if it has children)
                const children = wbsNodes.filter(n => String(n.parent_id) === String(parent.id));
                if (children.length > 0) {
                    const sum = children.reduce((acc, curr) => acc + parseFloat(curr.weight_percentage || 0), 0);
                    if (parseFloat(parent.weight_percentage) !== sum) {
                        parent.weight_percentage = sum;
                        hasChanges = true;
                    }
                }
            });
        }
        
        if (hasChanges) {
            renderWbsTable();
        }
        updateTotalLeafWeight();
    }

    function updateTotalLeafWeight() {
        const leafNodes = wbsNodes.filter(n => !wbsNodes.some(child => String(child.parent_id) === String(n.id)));
        const total = leafNodes.reduce((acc, curr) => acc + parseFloat(curr.weight_percentage || 0), 0);
        document.getElementById('totalLeafWeightLabel').innerText = total.toFixed(2);
        
        if(Math.abs(total - 100) > 0.01) {
            document.getElementById('totalLeafWeightLabel').parentElement.classList.add('text-danger');
        } else {
            document.getElementById('totalLeafWeightLabel').parentElement.classList.remove('text-danger');
            document.getElementById('totalLeafWeightLabel').parentElement.classList.add('text-success');
        }
    }

    function buildNodeHtml(node, level) {
        const padding = level * 30;
        const hasChildren = wbsNodes.some(n => String(n.parent_id) === String(node.id));
        
        let html = `
            <tr>
                <td style="padding-left: ${padding + 10}px;">
                    <div class="d-flex">
                        <i class="bi ${hasChildren ? 'bi-folder2-open text-warning' : 'bi-file-text text-secondary'} me-2 mt-1"></i>
                        <input type="text" class="form-control form-control-sm" value="${node.work_name}" onchange="updateNodeData('${node.id}', 'work_name', this.value)" placeholder="Nama Pekerjaan">
                    </div>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" value="${node.weight_percentage}" onchange="updateNodeData('${node.id}', 'weight_percentage', this.value)" ${hasChildren ? 'readonly bg-light' : ''}>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary" onclick="addWbsNode('${node.id}')" title="Tambah Child"><i class="bi bi-plus"></i> Child</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteWbsNode('${node.id}')" title="Hapus"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;
        
        // Children
        const children = wbsNodes.filter(n => String(n.parent_id) === String(node.id)).sort((a,b) => a.sort_order - b.sort_order);
        children.forEach(child => {
            html += buildNodeHtml(child, level + 1);
        });
        
        return html;
    }

    function renderWbsTable() {
        const tbody = document.getElementById('wbsBuilderBody');
        let html = '';
        
        // Root nodes
        const rootNodes = wbsNodes.filter(n => !n.parent_id).sort((a,b) => a.sort_order - b.sort_order);
        rootNodes.forEach(node => {
            html += buildNodeHtml(node, 0);
        });
        
        if(wbsNodes.length === 0) {
            html = `<tr><td colspan="3" class="text-center text-muted py-3">Belum ada WBS. Klik Tambah Parent Baru.</td></tr>`;
        }
        
        tbody.innerHTML = html;
        calculateParentWeights(); // implicitly calls updateTotalLeafWeight
    }

    function saveWbs() {
        const leafNodes = wbsNodes.filter(n => !wbsNodes.some(child => String(child.parent_id) === String(n.id)));
        const total = leafNodes.reduce((acc, curr) => acc + parseFloat(curr.weight_percentage || 0), 0);
        
        if (wbsNodes.length > 0 && Math.abs(total - 100) > 0.01) {
            Swal.fire('Error', 'Total bobot WBS leaf harus 100%. Total saat ini: ' + total.toFixed(2) + '%', 'error');
            return;
        }

        // Validate empty names
        if (wbsNodes.some(n => n.work_name.trim() === '')) {
            Swal.fire('Error', 'Nama pekerjaan tidak boleh kosong.', 'error');
            return;
        }

        // POST Data
        fetch(`{{ route('s-curves.save-wbs', $sCurve->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ wbs_data: JSON.stringify(wbsNodes) })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.fire('Berhasil', 'WBS berhasil disimpan.', 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
        });
    }


    // --- PLAN BUILDER ---
    function openPlanModal() {
        recalcPlanTotals();
        new bootstrap.Modal(document.getElementById('planModal')).show();
    }

    document.querySelectorAll('.plan-input').forEach(input => {
        input.addEventListener('change', recalcPlanTotals);
    });

    function recalcPlanTotals() {
        document.querySelectorAll('#planTable tbody tr').forEach(row => {
            const itemId = row.getAttribute('data-item-id');
            if(!itemId) return; // Skip parent rows

            const weight = parseFloat(row.getAttribute('data-weight')) || 0;
            let total = 0;
            row.querySelectorAll('.plan-input').forEach(inp => {
                total += parseFloat(inp.value || 0);
            });
            const totalCol = row.querySelector('.plan-total-col');
            totalCol.innerText = total.toFixed(2);
            
            if (Math.abs(total - weight) > 0.01) {
                totalCol.classList.add('text-danger');
                totalCol.classList.remove('text-success');
            } else {
                totalCol.classList.remove('text-danger');
                totalCol.classList.add('text-success');
            }
        });
    }

    function savePlans() {
        let plansData = {};
        let isValid = true;
        let errorMsg = '';

        document.querySelectorAll('#planTable tbody tr').forEach(row => {
            const itemId = row.getAttribute('data-item-id');
            if(!itemId) return; // Skip parent rows

            const weight = parseFloat(row.getAttribute('data-weight')) || 0;
            let total = 0;
            plansData[itemId] = {};
            
            row.querySelectorAll('.plan-input').forEach(inp => {
                const w = inp.getAttribute('data-week');
                const val = parseFloat(inp.value || 0);
                plansData[itemId][w] = val;
                total += val;
            });
            
            if (Math.abs(total - weight) > 0.01) {
                isValid = false;
                errorMsg = `Total distribusi plan untuk WBS harus sama dengan bobot (${weight.toFixed(2)}%).`;
            }
        });

        if (!isValid) {
            Swal.fire('Error Validasi', errorMsg, 'error');
            return;
        }

        fetch(`{{ route('s-curves.save-plans', $sCurve->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ plans: plansData })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.fire('Berhasil', 'Weekly Plan berhasil disimpan.', 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    }


    // --- ACTUAL BUILDER ---
    function openActualModal() {
        recalcActualTotals();
        new bootstrap.Modal(document.getElementById('actualModal')).show();
    }

    document.querySelectorAll('.actual-input').forEach(input => {
        input.addEventListener('change', recalcActualTotals);
    });

    function recalcActualTotals() {
        document.querySelectorAll('#actualTable tbody tr').forEach(row => {
            const itemId = row.getAttribute('data-item-id');
            if(!itemId) return; // Skip parent rows

            const weight = parseFloat(row.getAttribute('data-weight')) || 0;
            let total = 0;
            row.querySelectorAll('.actual-input').forEach(inp => {
                total += parseFloat(inp.value || 0);
            });
            const totalCol = row.querySelector('.actual-total-col');
            totalCol.innerText = total.toFixed(2);
            
            if (total > weight + 0.01) { // allow small float err
                totalCol.classList.add('text-danger');
            } else {
                totalCol.classList.remove('text-danger');
            }
        });
    }

    function saveActuals() {
        let actualsData = {};
        let isValid = true;
        let errorMsg = '';

        document.querySelectorAll('#actualTable tbody tr').forEach(row => {
            const itemId = row.getAttribute('data-item-id');
            if(!itemId) return; // Skip parent rows

            const weight = parseFloat(row.getAttribute('data-weight')) || 0;
            let total = 0;
            actualsData[itemId] = {};
            
            row.querySelectorAll('.actual-input').forEach(inp => {
                const w = inp.getAttribute('data-week');
                const val = parseFloat(inp.value || 0);
                actualsData[itemId][w] = val;
                total += val;
            });
            
            if (total > weight + 0.01) {
                isValid = false;
                errorMsg = `Total aktual untuk WBS tidak boleh melebihi bobot (${weight.toFixed(2)}%).`;
            }
        });

        if (!isValid) {
            Swal.fire('Error Validasi', errorMsg, 'error');
            return;
        }

        fetch(`{{ route('s-curves.save-actuals', $sCurve->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ actuals: actualsData })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.fire('Berhasil', 'Weekly Actual berhasil disimpan.', 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    }

</script>
@endsection
