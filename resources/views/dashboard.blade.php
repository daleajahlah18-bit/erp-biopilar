@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Overview')
@section('page_subtitle', 'Welcome to ERP PT Bio Pilar Utama')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Pendapatan (Bulan Ini)</div>
                    <div class="stat-value">Rp {{ number_format($totalSalesThisMonth, 0, ',', '.') }}</div>
                </div>
                <div class="stat-icon" style="background: #E8F5E9; color: #2E7D32;">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Pengeluaran (Bulan Ini)</div>
                    <div class="stat-value">Rp {{ number_format($totalPurchasesThisMonth, 0, ',', '.') }}</div>
                </div>
                <div class="stat-icon" style="background: #FFEBEE; color: #C62828;">
                    <i class="bi bi-cart-x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Proyek Aktif</div>
                    <div class="stat-value">{{ $activeProjects }}</div>
                </div>
                <div class="stat-icon" style="background: #E3F2FD; color: #1565C0;">
                    <i class="bi bi-briefcase"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Stok Menipis</div>
                    <div class="stat-value">{{ $lowStockProducts }}</div>
                </div>
                <div class="stat-icon" style="background: #FFF3E0; color: #EF6C00;">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Chart: Sales vs Purchases -->
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tren Penjualan vs Pembelian (12 Bulan)</h6>
            </div>
            <div class="card-body">
                <canvas id="salesPurchasesChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Project Statuses -->
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status Proyek</h6>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="projectStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Activities -->
    <div class="col-12">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terakhir</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Modul</th>
                                <th>Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <strong>{{ $log->causer->name ?? 'System' }}</strong>
                                </td>
                                <td>{{ $log->log_name ?? '-' }}</td>
                                <td>
                                    @php
                                        $bg = 'bg-secondary';
                                        if($log->event === 'created') $bg = 'bg-success';
                                        if($log->event === 'updated') $bg = 'bg-warning text-dark';
                                        if($log->event === 'deleted') $bg = 'bg-danger';
                                    @endphp
                                    <span class="badge {{ $bg }}">{{ ucfirst($log->event) }}</span> 
                                    {{ $log->description }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Belum ada aktivitas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales vs Purchases Chart
    const ctxSales = document.getElementById('salesPurchasesChart').getContext('2d');
    new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_reverse($months)) !!},
            datasets: [
                {
                    label: 'Penjualan (Sales)',
                    data: {!! json_encode(array_reverse($salesTrend)) !!},
                    borderColor: '#2E7D32',
                    backgroundColor: 'rgba(46, 125, 50, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Pembelian (Purchases)',
                    data: {!! json_encode(array_reverse($purchasesTrend)) !!},
                    borderColor: '#C62828',
                    backgroundColor: 'rgba(198, 40, 40, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000) + ' Jt';
                            }
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });

    // Project Status Donut Chart
    const projectStatuses = {!! json_encode($projectStatuses) !!};
    const labels = Object.keys(projectStatuses);
    const data = Object.values(projectStatuses);
    
    // Default colors
    const backgroundColors = labels.map(label => {
        if(label === 'In Progress') return '#1565C0';
        if(label === 'Completed') return '#2E7D32';
        if(label === 'Planning') return '#F57C00';
        if(label === 'On Hold') return '#C62828';
        return '#757575';
    });

    if(data.length > 0) {
        const ctxProject = document.getElementById('projectStatusChart').getContext('2d');
        new Chart(ctxProject, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });
    } else {
        // Fallback text if no data
        const canvas = document.getElementById('projectStatusChart');
        const ctx = canvas.getContext('2d');
        ctx.font = "14px Inter";
        ctx.fillStyle = "#999";
        ctx.textAlign = "center";
        ctx.fillText("Belum ada proyek", canvas.width/2, canvas.height/2);
    }
});
</script>
@endpush
