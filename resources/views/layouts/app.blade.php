<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP') Bio Pilar ERP</title>

    <link rel="icon" href="{{ asset('logo11.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">

    <!-- FOUC Prevention Script -->
    <script>
        (function() {
            var currentTheme = localStorage.getItem('theme');
            if (!currentTheme) {
                currentTheme = 'light';
            }
            document.documentElement.setAttribute('data-theme', currentTheme);
        })();
    </script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            background: var(--color-bg);
            color: var(--color-text-primary);
        }

        /* SIDEBAR */
        .sidebar {
            width: 240px;
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            background: var(--color-surface);
            border-right: 1px solid var(--color-border);
            display: flex;
            flex-direction: column;
            z-index: 200;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 20px;
            font-size: 15px;
            font-weight: 700;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 64px;
        }

        .sidebar-logo .logo-icon {
            width: 34px; height: 34px;
            background: var(--color-primary);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 17px;
        }

        .sidebar-logo .logo-text {
            font-size: 1.15rem; font-weight: 700; color: #1e293b;
            letter-spacing: -0.01em; white-space: nowrap;
        }

        .sidebar-nav {
            padding: 10px 0;
        }
        
        .sidebar-nav .nav-item {
            margin-bottom: 2px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link:focus {
            background: var(--color-primary-light);
            color: var(--color-primary-dark);
            outline: none;
        }

        .sidebar-nav .nav-link.active-parent {
            background: var(--color-primary-light);
            color: var(--color-primary);
            border-left-color: var(--color-primary);
            font-weight: 600;
        }

        .sidebar-nav .nav-link i { 
            font-size: 16px; 
            width: 18px; 
            text-align: center;
        }

        .sidebar-nav .arrow-icon {
            margin-left: auto;
            font-size: 12px !important;
            transition: transform 0.25s ease;
        }

        .sidebar-nav .nav-link:not(.collapsed) .arrow-icon {
            transform: rotate(180deg);
        }

        .sidebar-nav .sub-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            background: rgba(0, 0, 0, 0.02);
        }
        
        html[data-theme="dark"] .sidebar-nav .sub-menu {
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-nav .sub-item {
            display: flex;
            align-items: center;
            padding: 8px 20px 8px 50px;
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .sidebar-nav .sub-item:hover {
            color: var(--color-primary);
        }

        .sidebar-nav .sub-item.active {
            color: var(--color-primary);
            font-weight: 600;
            background: transparent;
        }
        
        .sidebar-nav .sub-item.active::before {
            content: '';
            position: absolute;
            left: 20px;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--color-primary);
        }

        /* TOPBAR */
        .topbar {
            position: fixed;
            top: 0; left: 240px; right: 0;
            height: 60px;
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            padding: 0 32px;
            justify-content: space-between;
            z-index: 100;
        }

        .breadcrumb { font-size: 13px; margin: 0; }
        .breadcrumb-item a { color: var(--color-text-secondary); text-decoration: none; }
        .breadcrumb-item.active { color: var(--color-text-primary); font-weight: 500; }

        /* MAIN */
        .main-wrapper {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
        }

        .page-content { padding: 32px; }

        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--color-text-secondary); margin: 0; }

        /* CARD */
        .card {
            background: var(--color-surface);
            border: 1px solid var(--color-border) !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        }
        .card-body { padding: 24px; }

        /* FORM */
        .form-label {
            font-size: 12.5px;
            font-weight: 500;
            color: var(--color-text-secondary);
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            background: var(--color-input-bg);
            border: 1.5px solid var(--color-border);
            border-radius: 8px;
            padding: 10px 14px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--color-text-primary);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--color-primary) !important;
            box-shadow: 0 0 0 3px var(--color-primary-light) !important;
            outline: none;
            background: #fff;
        }

        /* BUTTONS */
        .btn-primary-custom {
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 22px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary-custom:hover { background: var(--color-primary-dark); color: #fff; }
        
        .btn-outline-custom {
            background: var(--color-surface);
            color: var(--color-text-primary);
            border: 1.5px solid var(--color-border);
            border-radius: 8px;
            padding: 9px 16px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: border-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-outline-custom:hover { border-color: var(--color-primary); color: var(--color-primary); }

        .btn-danger-custom {
            background: #FFEBEE;
            color: #C62828;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
        }

        /* TABLE */
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom thead th {
            background: var(--color-bg);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--color-text-secondary);
            padding: 12px 16px;
            border-bottom: 1px solid var(--color-border);
            white-space: nowrap;
        }
        .table-custom tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--color-border);
            font-size: 13.5px;
            vertical-align: middle;
        }
        .table-custom tbody tr:hover { background: #FAFAFA; }
        .table-custom tbody tr:last-child td { border-bottom: none; }

        /* BADGES */
        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
        }
        .badge-draft      { background: #F5F5F5; color: #757575; }
        .badge-approved   { background: #E3F2FD; color: #1565C0; }
        .badge-released   { background: #E3F2FD; color: #1565C0; }
        .badge-inprogress { background: #FFF3E0; color: #E65100; }
        .badge-finished   { background: #E8F5E9; color: #2E7D32; }
        .badge-closed     { background: #E8F5E9; color: #2E7D32; }
        .badge-cancelled  { background: #FFEBEE; color: #C62828; }
        .badge-lunas      { background: #E8F5E9; color: #2E7D32; }
        .badge-sebagian   { background: #FFF8E1; color: #F57F17; }
        .badge-belum      { background: #FFEBEE; color: #C62828; }

        /* STAT CARD */
        .stat-card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 22px 24px;
        }
        .stat-card .stat-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--color-text-secondary);
            margin-bottom: 8px;
        }
        .stat-card .stat-value {
            font-size: 30px;
            font-weight: 700;
            color: var(--color-text-primary);
            line-height: 1.1;
        }
        .stat-card .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
    </style>

    @stack('styles')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            border-radius: 14px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
            background-color: var(--color-surface, #ffffff) !important;
            color: var(--color-text-primary, #333333) !important;
        }
        .swal2-title {
            font-weight: 700 !important;
        }
        .swal2-confirm {
            background-color: var(--color-primary, #6D4CFF) !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 10px 24px !important;
        }
        .swal2-cancel {
            background-color: #e5e7eb !important;
            color: #374151 !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 10px 24px !important;
        }
        .swal2-backdrop-show {
            background: rgba(0,0,0,0.4) !important;
            backdrop-filter: blur(2px) !important;
        }
    </style>
    <script>
        function notifySuccess(title = 'Berhasil!', text = '') {
            return Swal.fire({
                icon: 'success',
                title: title,
                text: text,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        }
        function notifyError(title = 'Terjadi Kesalahan', text = '') {
            // Truncate long server errors
            if(text.length > 300) {
                text = text.substring(0, 300) + '...';
            }
            return Swal.fire({
                icon: 'error',
                title: title,
                text: text,
                confirmButtonText: 'Tutup'
            });
        }
        function confirmDelete(callback, title = 'Hapus Data?', text = 'Data yang dihapus tidak dapat dikembalikan.') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                iconHtml: '<i class="bi bi-trash text-danger"></i>',
                customClass: {
                    icon: 'border-0'
                },
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#e5e7eb',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }
        function confirmAction(callback, title = 'Konfirmasi', text = 'Apakah Anda yakin?') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }
    </script>
</head>
<body>

    <!-- SIDEBAR OVERLAY -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('logo11.png') }}" alt="Logo" style="width: 34px; height: auto;">
            <div class="logo-text">
                ERP Bio Pilar
            </div>
        </div>

        <div class="sidebar-nav pb-4" id="sidebarNav">
            
            {{-- DASHBOARD --}}
            @can('dashboard.visible')
            @php $isActive = request()->is('dashboard*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuDashboard" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuDashboard" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        <li>
                            <a href="/dashboard" class="sub-item {{ request()->is('dashboard*') ? 'active' : '' }}">
                                Dashboard Overview
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            @endcan

            {{-- MASTER DATA --}}
            @canany(['products.visible', 'suppliers.visible', 'customers.visible', 'units.visible', 'projects.visible', 'warehouses.visible'])
            @php $isActive = request()->is('master/*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuMaster" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-database"></i>
                    <span>Master Data</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuMaster" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        @can('products.visible')
                        <li>
                            <a href="/master/products" class="sub-item {{ request()->is('master/products*') ? 'active' : '' }}">Produk</a>
                        </li>
                        @endcan
                        @can('suppliers.visible')
                        <li>
                            <a href="/master/suppliers" class="sub-item {{ request()->is('master/suppliers*') ? 'active' : '' }}">Supplier</a>
                        </li>
                        @endcan
                        @can('customers.visible')
                        <li>
                            <a href="/master/customers" class="sub-item {{ request()->is('master/customers*') ? 'active' : '' }}">Customer</a>
                        </li>
                        @endcan
                        @can('units.visible')
                        <li>
                            <a href="/master/units" class="sub-item {{ request()->is('master/units*') ? 'active' : '' }}">Satuan (Unit)</a>
                        </li>
                        @endcan
                        @can('projects.visible')
                        <li>
                            <a href="/master/projects" class="sub-item {{ request()->is('master/projects*') ? 'active' : '' }}">Master Proyek</a>
                        </li>
                        @endcan
                        @can('warehouses.visible')
                        <li>
                            <a href="/master/warehouses" class="sub-item {{ request()->is('master/warehouses*') ? 'active' : '' }}">Gudang</a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endif

            {{-- PURCHASING --}}
            @canany(['purchase_order.visible', 'goods_receipt.visible', 'purchase_payment.visible', 'accounts_payable.visible'])
            @php $isActive = request()->is('purchasing/*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuPurchasing" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-cart"></i>
                    <span>Purchasing</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuPurchasing" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        @can('purchase_order.visible')
                        <li><a href="/purchasing/purchase-orders" class="sub-item {{ request()->is('purchasing/purchase-orders*') ? 'active' : '' }}">Purchase Release</a></li>
                        @endcan
                        @can('goods_receipt.visible')
                        <li><a href="/purchasing/goods-receipts" class="sub-item {{ request()->is('purchasing/goods-receipts*') ? 'active' : '' }}">Goods Receipt</a></li>
                        @endcan
                        @can('purchase_payment.visible')
                        <li><a href="/purchasing/payments" class="sub-item {{ request()->is('purchasing/payments*') ? 'active' : '' }}">Pembayaran (Payment)</a></li>
                        @endcan
                        @can('accounts_payable.visible')
                        <li><a href="/purchasing/payables" class="sub-item {{ request()->is('purchasing/payables*') ? 'active' : '' }}">Hutang (Payables)</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endif

            {{-- PRODUCTION --}}
            @canany(['bill_of_material.visible', 'production_order.visible', 'project_production.visible'])
            @php $isActive = request()->is('production/*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuProduction" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-gear"></i>
                    <span>Production</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuProduction" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        @can('bill_of_material.visible')
                        <li><a href="/production/bom" class="sub-item {{ request()->is('production/bom*') ? 'active' : '' }}">Bill of Material</a></li>
                        @endcan
                        @can('production_order.visible')
                        <li><a href="/production/orders" class="sub-item {{ request()->is('production/orders*') ? 'active' : '' }}">Production Order</a></li>
                        @endcan
                        @can('project_production.visible')
                        <li><a href="/production/project-productions" class="sub-item {{ request()->is('production/project-productions*') ? 'active' : '' }}">Project Fabrication</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endif

            {{-- SALES --}}
            @canany(['sales_order.visible', 'sales_invoice.visible', 'sales_payment.visible', 'accounts_receivable.visible'])
            @php $isActive = request()->is('sales/*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuSales" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-shop"></i>
                    <span>Sales</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuSales" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        @can('sales_order.visible')
                        <li><a href="/sales/orders" class="sub-item {{ request()->is('sales/orders*') ? 'active' : '' }}">Sales Order</a></li>
                        @endcan
                        @can('sales_invoice.visible')
                        <li><a href="/sales/invoices" class="sub-item {{ request()->is('sales/invoices*') ? 'active' : '' }}">Sales Invoice</a></li>
                        @endcan
                        @can('sales_payment.visible')
                        <li><a href="/sales/payments" class="sub-item {{ request()->is('sales/payments*') ? 'active' : '' }}">Sales Payment</a></li>
                        @endcan
                        @can('accounts_receivable.visible')
                        <li><a href="/sales/receivables" class="sub-item {{ request()->is('sales/receivables*') ? 'active' : '' }}">Piutang (Receivables)</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endif

            {{-- PROJECT REPORT --}}
            @canany(['project_report.visible', 'daily_report.visible', 'report_phase.visible', 'survey_report.visible'])
            @php $isActive = request()->is('project-reports*') || request()->is('daily-reports*') || request()->is('report-phases*') || request()->is('survey-reports*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuProjectReport" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Project Report</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuProjectReport" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        @can('project_report.visible')
                        <li><a href="/project-reports" class="sub-item {{ request()->is('project-reports*') ? 'active' : '' }}">Project Progress</a></li>
                        @endcan
                        @can('daily_report.visible')
                        <li><a href="{{ route('daily-reports.index') }}" class="sub-item {{ request()->is('daily-reports*') ? 'active' : '' }}">Daily Report</a></li>
                        @endcan
                        @can('report_phase.visible')
                        <li><a href="{{ route('report-phases.index') }}" class="sub-item {{ request()->is('report-phases*') ? 'active' : '' }}">Report Phase</a></li>
                        @endcan
                        @can('survey_report.visible')
                        <li><a href="{{ route('survey-reports.index') }}" class="sub-item {{ request()->is('survey-reports*') ? 'active' : '' }}">Survey Report</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endif

            {{-- INVENTORY --}}
            @canany(['product_stock.visible', 'stock_transfer.visible', 'stock_opname.visible', 'item_journal.visible'])
            @php $isActive = request()->is('inventory/*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuInventory" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventory</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuInventory" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        @can('product_stock.visible')
                        <li><a href="/inventory/stocks" class="sub-item {{ request()->is('inventory/stocks*') ? 'active' : '' }}">Stok Produk</a></li>
                        @endcan
                        @can('stock_transfer.visible')
                        <li><a href="/inventory/transfers" class="sub-item {{ request()->is('inventory/transfers*') ? 'active' : '' }}">Transfer Stok</a></li>
                        @endcan
                        @can('stock_opname.visible')
                        <li><a href="/inventory/stock-opnames" class="sub-item {{ request()->is('inventory/stock-opnames*') ? 'active' : '' }}">Stock Opname</a></li>
                        @endcan
                        @can('item_journal.visible')
                        <li><a href="/inventory/item-journals" class="sub-item {{ request()->is('inventory/item-journals*') ? 'active' : '' }}">Item Journal</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endif

            {{-- ASSET MANAGEMENT --}}
            @canany(['asset_dashboard.visible', 'master_categories.visible', 'master_assets.visible', 'asset_reports.visible'])
            @php $isActive = request()->is('asset-management/*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuAsset" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-pc-display"></i>
                    <span>Asset Management</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuAsset" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        @can('asset_dashboard.visible')
                        <li><a href="{{ route('asset-management.dashboard') }}" class="sub-item {{ request()->is('asset-management/dashboard*') ? 'active' : '' }}">Dashboard</a></li>
                        @endcan
                        @can('master_categories.visible')
                        <li><a href="{{ route('asset-management.categories.index') }}" class="sub-item {{ request()->is('asset-management/categories*') ? 'active' : '' }}">Master Categories</a></li>
                        @endcan
                        @can('master_assets.visible')
                        <li><a href="{{ route('asset-management.assets.index') }}" class="sub-item {{ request()->is('asset-management/assets*') ? 'active' : '' }}">Master Assets</a></li>
                        @endcan
                        @can('asset_reports.visible')
                        <li><a href="{{ route('asset-management.reports.index') }}" class="sub-item {{ request()->is('asset-management/reports*') ? 'active' : '' }}">Asset Reports</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endif

            {{-- USER MANAGEMENT --}}
            @canany(['users.visible', 'roles.visible', 'activity_logs.visible'])
            @php $isActive = request()->is('user-management/*'); @endphp
            <div class="nav-item">
                <a class="nav-link {{ $isActive ? 'active-parent' : 'collapsed' }}" href="#submenuUser" data-bs-toggle="collapse" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    <i class="bi bi-people"></i>
                    <span>User Management</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse {{ $isActive ? 'show' : '' }}" id="submenuUser" data-bs-parent="#sidebarNav">
                    <ul class="sub-menu">
                        @can('users.visible')
                        <li><a href="{{ route('users.index') }}" class="sub-item {{ request()->is('user-management/users*') ? 'active' : '' }}">Users</a></li>
                        @endcan
                        @can('roles.visible')
                        <li><a href="{{ route('roles.index') }}" class="sub-item {{ request()->is('user-management/roles*') ? 'active' : '' }}">Roles</a></li>
                        @endcan
                        @can('activity_logs.visible')
                        <li><a href="{{ route('activity-logs.index') }}" class="sub-item {{ request()->is('user-management/activity-logs*') ? 'active' : '' }}">Activity Logs</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button id="hamburgerBtn" class="btn-hamburger d-lg-none" title="Menu">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="d-none d-md-block">
                @yield('breadcrumb')
            </div>
            <div class="d-md-none text-truncate" style="max-width: 150px; font-weight: 600;">
                @yield('page_title')
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button id="themeToggleBtn" class="theme-toggle" title="Toggle Theme">
                <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
            </button>
            <i class="bi bi-bell fs-5 text-secondary"></i>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center gap-2 text-decoration-none " data-bs-toggle="dropdown">
                    <div style="width: 32px; height: 32px; background: var(--color-primary); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-wrapper">
        <div class="page-content">
            <div class="page-header d-flex justify-content-between align-items-end">
                <div>
                    <h1>@yield('page_title')</h1>
                    <p>@yield('page_subtitle')</p>
                </div>
                <div>
                    @yield('header_actions')
                </div>
            </div>

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <style>
        .select2-container .select2-selection--single {
            height: 42px; /* Match form-control */
            border: 1.5px solid var(--color-border);
            border-radius: 8px;
            background: var(--color-input-bg);
            padding: 5px 14px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 10px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            color: var(--color-text-primary);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const overlay = document.getElementById('sidebarOverlay');
            const body = document.body;

            // --- 1. Responsive Sidebar Toggle ---
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
                
                // Prevent body scroll when mobile sidebar is open
                if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
                    body.style.overflow = 'hidden';
                } else {
                    body.style.overflow = '';
                }
            }

            if (hamburgerBtn) hamburgerBtn.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);

            // Close sidebar on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                    toggleSidebar();
                }
            });

            // Auto-close sidebar on mobile when a link is clicked
            const sidebarLinks = sidebar.querySelectorAll('.sub-item, a:not([data-bs-toggle="collapse"])');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
                        toggleSidebar();
                    }
                });
            });

            // Reset sidebar state on window resize (prevent bugs)
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('show');
                    body.style.overflow = '';
                }
            });

            // --- 2. Navigation State Preservation ---
            // Ensure the active menu's parent (if any) is expanded.
            const activeMenu = sidebar.querySelector('.sub-item.active, .nav-link.active-parent');
            if (activeMenu) {
                // Scroll the sidebar to show active item
                setTimeout(() => {
                    activeMenu.scrollIntoView({ block: 'center', behavior: 'smooth' });
                }, 300);
            }

            // --- 3. Scroll Position Restoration ---
            const position = sessionStorage.getItem('sidebar-scroll-position');
            if (position !== null) {
                sidebar.scrollTop = parseInt(position);
            }
            sidebar.addEventListener('scroll', function () {
                sessionStorage.setItem('sidebar-scroll-position', sidebar.scrollTop);
            });
        });
    </script>
    
    <!-- Theme Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('themeToggleBtn');
            const toggleIcon = document.getElementById('themeToggleIcon');
            
            function updateIcon(theme) {
                if (theme === 'dark') {
                    toggleIcon.classList.remove('bi-moon-stars');
                    toggleIcon.classList.add('bi-sun');
                } else {
                    toggleIcon.classList.remove('bi-sun');
                    toggleIcon.classList.add('bi-moon-stars');
                }
            }

            // Initialize icon on load
            const currentTheme = document.documentElement.getAttribute('data-theme');
            updateIcon(currentTheme);

            toggleBtn.addEventListener('click', function() {
                let theme = document.documentElement.getAttribute('data-theme');
                let newTheme = theme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
            });
        });
    </script>
    
    <!-- Session Timeout Overlay -->
    <div id="sessionWarningOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; color: white;">
        <div style="background: #222; padding: 30px; border-radius: 8px; text-align: center; max-width: 400px;">
            <h4>Session Expiring</h4>
            <p>Your session will expire in 2 minutes due to inactivity. Do you want to stay logged in?</p>
            <button id="stayLoggedInBtn" class="btn btn-primary mt-3">Stay Logged In</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let sessionTimeout = 30 * 60 * 1000; // 30 minutes
            let warningTime = 2 * 60 * 1000; // 2 minutes before
            let timeoutTimer;
            let warningTimer;
            let overlay = document.getElementById('sessionWarningOverlay');
            let isWarningActive = false;

            function resetTimers() {
                if (isWarningActive) return; // Don't reset if warning is showing

                clearTimeout(timeoutTimer);
                clearTimeout(warningTimer);

                warningTimer = setTimeout(() => {
                    isWarningActive = true;
                    overlay.style.display = 'flex';
                }, sessionTimeout - warningTime);

                timeoutTimer = setTimeout(() => {
                    document.getElementById('logout-form').submit();
                }, sessionTimeout);
            }

            document.getElementById('stayLoggedInBtn').addEventListener('click', function() {
                isWarningActive = false;
                overlay.style.display = 'none';
                fetch('{{ url('/') }}'); // keep alive request
                resetTimers();
            });

            // Reset timers on activity
            document.addEventListener('mousemove', resetTimers);
            document.addEventListener('keypress', resetTimers);
            document.addEventListener('scroll', resetTimers);
            document.addEventListener('click', resetTimers);

            resetTimers();
        });
    </script>

    <!-- Global Notification Handlers -->
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                notifySuccess('Berhasil!', '{!! addslashes(session('success')) !!}');
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                notifyError('Terjadi Kesalahan', '{!! addslashes(session('error')) !!}');
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({icon: 'warning', title: 'Perhatian', text: '{!! addslashes(session('warning')) !!}'});
            });
        </script>
    @endif

    @if (session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({icon: 'info', title: 'Informasi', text: '{!! addslashes(session('info')) !!}'});
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let errorMessages = '';
                @foreach ($errors->all() as $error)
                    errorMessages += '• {!! addslashes($error) !!}\n';
                @endforeach
                notifyError('Validasi Gagal', errorMessages);
            });
        </script>
    @endif

    <!-- Global Submit Button State Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('submit', function(e) {
                if(e.target && e.target.tagName === 'FORM' && !e.target.classList.contains('no-loading')) {
                    let btn = e.target.querySelector('button[type="submit"]');
                    if(btn && !btn.disabled) {
                        btn.dataset.originalText = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';
                        
                        // Restore button after a short delay if it's a download target
                        setTimeout(() => {
                            if(btn && (e.target.target === '_blank' || e.target.classList.contains('download-form'))) {
                                btn.innerHTML = btn.dataset.originalText;
                                btn.disabled = false;
                            }
                        }, 2000);
                    }
                }
            });

            // Restore state when user navigates back via browser history
            window.addEventListener('pageshow', function(event) {
                document.querySelectorAll('button[type="submit"]').forEach(btn => {
                    if(btn.dataset.originalText) {
                        btn.innerHTML = btn.dataset.originalText;
                        btn.disabled = false;
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>

