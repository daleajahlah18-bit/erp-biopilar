<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // API Routes for internal AJAX calls
    Route::get('/api/products/{id}', [\App\Http\Controllers\Api\ProductApiController::class, 'show'])->name('api.products.show');

    Route::get('/download/{module}/{id}/{field}', [\App\Http\Controllers\DownloadController::class, 'download'])->name('download');
    Route::get('/display/{module}/{id}/{field}', [\App\Http\Controllers\DownloadController::class, 'display'])->name('display');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        // Products Import Routes
        Route::prefix('products/import')->name('products.import.')->middleware('permission:products.import')->group(function () {
            Route::get('template', [\App\Http\Controllers\Master\ProductController::class, 'downloadTemplate'])->name('template');
            Route::post('upload', [\App\Http\Controllers\Master\ProductController::class, 'uploadImport'])->name('upload');
            Route::post('process', [\App\Http\Controllers\Master\ProductController::class, 'processImport'])->name('process');
            Route::get('errors/{id}', [\App\Http\Controllers\Master\ProductController::class, 'downloadErrors'])->name('errors');
        });

        Route::resource('products', App\Http\Controllers\Master\ProductController::class)->middleware('permission:products.visible');
        Route::resource('suppliers', App\Http\Controllers\Master\SupplierController::class)->middleware('permission:suppliers.visible');
        Route::resource('units', App\Http\Controllers\Master\UnitController::class)->middleware('permission:units.visible');
        Route::resource('customers', App\Http\Controllers\Master\CustomerController::class)->middleware('permission:customers.visible');
        Route::get('projects/{project}/pdf', [App\Http\Controllers\Master\ProjectController::class, 'pdf'])->name('projects.pdf')->middleware('permission:projects.visible');
        Route::resource('projects', App\Http\Controllers\Master\ProjectController::class)->middleware('permission:projects.visible');
        Route::resource('warehouses', App\Http\Controllers\Master\WarehouseController::class)->middleware('permission:warehouses.visible');
    });

    // Purchasing
    Route::prefix('purchasing')->name('purchasing.')->group(function () {
        Route::middleware('permission:purchase_order.visible')->group(function () {
            Route::get('purchase-orders/{purchase_order}/pdf', [App\Http\Controllers\Purchasing\PurchaseOrderController::class, 'printPdf'])->name('purchase-orders.pdf');
            Route::resource('purchase-orders', App\Http\Controllers\Purchasing\PurchaseOrderController::class);
        });
        
        Route::middleware('permission:goods_receipt.visible')->group(function () {
            Route::get('goods-receipts/api/po-details/{id}', [App\Http\Controllers\Purchasing\GoodsReceiptController::class, 'getPoDetails'])->name('goods-receipts.api.po-details');
            Route::get('goods-receipts/{goods_receipt}/pdf', [App\Http\Controllers\Purchasing\GoodsReceiptController::class, 'printPdf'])->name('goods-receipts.pdf');
            Route::resource('goods-receipts', App\Http\Controllers\Purchasing\GoodsReceiptController::class);
        });

        // Purchase Payments
        Route::middleware('permission:purchase_payment.visible')->group(function () {
            Route::get('payments/api/gr-info/{id}', [App\Http\Controllers\Purchasing\PurchasePaymentController::class, 'getGrInfo']);
            Route::get('payments/{payment}/pdf', [App\Http\Controllers\Purchasing\PurchasePaymentController::class, 'printPdf'])->name('payments.pdf');
            Route::resource('payments', App\Http\Controllers\Purchasing\PurchasePaymentController::class);
        });

        // Purchase Payables
        Route::middleware('permission:accounts_payable.visible')->group(function () {
            Route::get('payables', [App\Http\Controllers\Purchasing\PurchasePayableController::class, 'index'])->name('payables.index');
            Route::get('payables/{goods_receipt}', [App\Http\Controllers\Purchasing\PurchasePayableController::class, 'show'])->name('payables.show');
        });
    });

    // Production
    Route::prefix('production')->name('production.')->group(function () {
        Route::middleware('permission:bill_of_material.visible')->group(function () {
            Route::get('bom/api/product-info/{id}', [App\Http\Controllers\Production\BillOfMaterialController::class, 'getProductInfo']);
            Route::resource('bom', App\Http\Controllers\Production\BillOfMaterialController::class);
        });
        
        Route::middleware('permission:production_order.visible')->group(function () {
            Route::get('orders/api/bom/{bom_id}', [App\Http\Controllers\Production\ProductionOrderController::class, 'getBomMaterials']);
            Route::get('orders/api/stock/{warehouse_id}/{product_id}', [App\Http\Controllers\Production\ProductionOrderController::class, 'getWarehouseStock']);
            Route::post('orders/{order}/start-production', [App\Http\Controllers\Production\ProductionOrderController::class, 'startProduction'])->name('orders.start-production');
            Route::get('orders/{order}/production-result', [App\Http\Controllers\Production\ProductionOrderController::class, 'productionResult'])->name('orders.production-result');
            Route::post('orders/{order}/save-production-result', [App\Http\Controllers\Production\ProductionOrderController::class, 'saveProductionResult'])->name('orders.save-production-result');
            Route::resource('orders', App\Http\Controllers\Production\ProductionOrderController::class);
        });

        Route::middleware('permission:project_production.visible')->group(function () {
            Route::get('project-productions/api/stock/{warehouse_id}/{product_id}', [App\Http\Controllers\Production\ProjectProductionController::class, 'getProductStock']);
            Route::post('project-productions/{project_production}/finalize', [App\Http\Controllers\Production\ProjectProductionController::class, 'finalize'])->name('project-productions.finalize');
            Route::get('project-productions/{project_production}/pdf', [App\Http\Controllers\Production\ProjectProductionController::class, 'printPdf'])->name('project-productions.pdf');
            Route::resource('project-productions', App\Http\Controllers\Production\ProjectProductionController::class)->except(['destroy']);
        });
    });

    // Inventory
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::middleware('permission:product_stock.visible')->group(function () {
            Route::get('stocks/export', [App\Http\Controllers\Inventory\StockController::class, 'export'])->name('stocks.export');
            Route::get('stocks', [App\Http\Controllers\Inventory\StockController::class, 'index'])->name('stocks.index');
        });
        
        // API untuk AJAX Transfer Stok
        Route::middleware('permission:stock_transfer.visible')->group(function () {
            Route::get('transfers/api/products/{warehouseId}', [App\Http\Controllers\Inventory\InventoryTransferController::class, 'getProductsByWarehouse']);
            Route::get('transfers/api/stock/{warehouseId}/{productId}', [App\Http\Controllers\Inventory\InventoryTransferController::class, 'getProductStock']);
            Route::resource('transfers', App\Http\Controllers\Inventory\InventoryTransferController::class);
        });

        // Stock Opname
        Route::middleware('permission:stock_opname.visible')->group(function () {
            Route::get('stock-opnames/api/products/{warehouseId}', [App\Http\Controllers\Inventory\StockOpnameController::class, 'getProducts']);
            Route::resource('stock-opnames', App\Http\Controllers\Inventory\StockOpnameController::class);
        });
        
        // Item Journal
        Route::middleware('permission:item_journal.visible')->group(function () {
            Route::resource('item-journals', App\Http\Controllers\Inventory\ItemJournalController::class)->only(['index', 'create', 'store', 'show']);
        });
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        // Sales Orders
        Route::middleware('permission:sales_order.visible')->group(function () {
            Route::get('orders/api/project-info/{id}', [App\Http\Controllers\Sales\SalesOrderController::class, 'getProjectInfo']);
            Route::get('orders/api/product-info/{id}', [App\Http\Controllers\Sales\SalesOrderController::class, 'getProductInfo']);
            Route::get('orders/{order}/pdf', [App\Http\Controllers\Sales\SalesOrderController::class, 'printPdf'])->name('orders.pdf');
            Route::resource('orders', App\Http\Controllers\Sales\SalesOrderController::class);
        });

        // Sales Invoices
        Route::middleware('permission:sales_invoice.visible')->group(function () {
            Route::get('invoices/api/order-details/{id}', [App\Http\Controllers\Sales\SalesInvoiceController::class, 'getOrderDetails']);
            Route::post('invoices/{invoice}/approve', [App\Http\Controllers\Sales\SalesInvoiceController::class, 'approve'])->name('invoices.approve');
            Route::get('invoices/{invoice}/pdf', [App\Http\Controllers\Sales\SalesInvoiceController::class, 'printPdf'])->name('invoices.pdf');
            Route::resource('invoices', App\Http\Controllers\Sales\SalesInvoiceController::class);
        });

        // Sales Payments
        Route::middleware('permission:sales_payment.visible')->group(function () {
            Route::get('payments/api/invoice-info/{id}', [App\Http\Controllers\Sales\SalesPaymentController::class, 'getInvoiceInfo']);
            Route::get('payments/{payment}/pdf', [App\Http\Controllers\Sales\SalesPaymentController::class, 'printPdf'])->name('payments.pdf');
            Route::resource('payments', App\Http\Controllers\Sales\SalesPaymentController::class);
        });

        // Sales Receivables
        Route::middleware('permission:accounts_receivable.visible')->group(function () {
            Route::get('receivables', [App\Http\Controllers\Sales\SalesReceivableController::class, 'index'])->name('receivables.index');
            Route::get('receivables/{invoice}', [App\Http\Controllers\Sales\SalesReceivableController::class, 'show'])->name('receivables.show');
        });
    });

    // Project Reports
    Route::prefix('project-reports')->name('project-reports.')->middleware('permission:project_report.visible')->group(function () {
        Route::get('/', [App\Http\Controllers\Reports\ProjectReportController::class, 'index'])->name('index');
        Route::get('{project}', [App\Http\Controllers\Reports\ProjectReportController::class, 'show'])->name('show');
        Route::get('{project}/pdf', [App\Http\Controllers\Reports\ProjectReportController::class, 'printPdf'])->name('pdf');
    });

    // Daily Reports
    Route::middleware('permission:daily_report.visible')->group(function () {
        Route::get('daily-reports/{daily_report}/pdf', [App\Http\Controllers\ProjectReport\DailyReportController::class, 'exportPdf'])->name('daily-reports.pdf');
        Route::resource('daily-reports', App\Http\Controllers\ProjectReport\DailyReportController::class);
    });

    // Report Phases
    Route::middleware('permission:report_phase.visible')->group(function () {
        Route::get('report-phases/api/project-details/{id}', [App\Http\Controllers\ProjectReport\ReportPhaseController::class, 'getProjectDetails']);
        Route::get('report-phases/{report_phase}/pdf', [App\Http\Controllers\ProjectReport\ReportPhaseController::class, 'exportPdf'])->name('report-phases.pdf');
        Route::resource('report-phases', App\Http\Controllers\ProjectReport\ReportPhaseController::class);
    });

    // Survey Reports
    Route::middleware('permission:survey_report.visible')->group(function () {
        Route::get('survey-reports/{id}/pdf', [App\Http\Controllers\ProjectReport\SurveyReportController::class, 'pdf'])->name('survey-reports.pdf');
        Route::resource('survey-reports', App\Http\Controllers\ProjectReport\SurveyReportController::class);
    });

    // RABs
    Route::middleware('permission:rab.visible')->group(function () {
        Route::get('rabs/excel/template', [App\Http\Controllers\ProjectReport\RabExcelController::class, 'downloadTemplate'])->name('rabs.excel.template');
        Route::post('rabs/excel/preview', [App\Http\Controllers\ProjectReport\RabExcelController::class, 'previewImport'])->name('rabs.excel.preview');
        Route::post('rabs/excel/import', [App\Http\Controllers\ProjectReport\RabExcelController::class, 'import'])->name('rabs.excel.import');
        Route::get('rabs/{rab}/export', [App\Http\Controllers\ProjectReport\RabExcelController::class, 'export'])->name('rabs.export');
        Route::resource('rabs', App\Http\Controllers\ProjectReport\RabController::class);
    });

    // Project Documents
    Route::prefix('project-documents')->name('project-documents.')->middleware('permission:project_report.visible')->group(function () {
        Route::get('/', [App\Http\Controllers\Reports\ProjectDocumentController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Reports\ProjectDocumentController::class, 'store'])->name('store');
        Route::delete('{id}', [App\Http\Controllers\Reports\ProjectDocumentController::class, 'destroy'])->name('destroy');
    });
});



require __DIR__.'/auth.php';

// Asset Management Module
Route::prefix('asset-management')->name('asset-management.')->middleware('auth')->group(function () {
    Route::middleware('permission:asset_dashboard.visible')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AssetManagement\AssetDashboardController::class, 'index'])->name('dashboard');
        Route::post('/run-depreciation', [\App\Http\Controllers\AssetManagement\AssetDashboardController::class, 'runDepreciation'])->name('run-depreciation');
    });
    
    Route::middleware('permission:master_categories.visible')->group(function () {
        Route::resource('categories', \App\Http\Controllers\AssetManagement\AssetCategoryController::class);
    });
    
    Route::middleware('permission:master_assets.visible')->group(function () {
        // Excel Import Export
        Route::get('assets/export', [\App\Http\Controllers\AssetManagement\AssetController::class, 'export'])->name('assets.export');
        Route::get('assets/import/template', [\App\Http\Controllers\AssetManagement\AssetController::class, 'downloadTemplate'])->name('assets.import.template');
        Route::post('assets/import/upload', [\App\Http\Controllers\AssetManagement\AssetController::class, 'uploadImport'])->name('assets.import.upload');
        Route::post('assets/import/process', [\App\Http\Controllers\AssetManagement\AssetController::class, 'processImport'])->name('assets.import.process');
        Route::get('assets/import/errors/{id}', [\App\Http\Controllers\AssetManagement\AssetController::class, 'downloadErrors'])->name('assets.import.errors');

        Route::resource('assets', \App\Http\Controllers\AssetManagement\AssetController::class);
        
        // Asset sub-actions
        Route::post('assets/{asset}/maintenance', [\App\Http\Controllers\AssetManagement\AssetController::class, 'storeMaintenance'])->name('assets.maintenance.store');
        Route::post('assets/{asset}/improvement', [\App\Http\Controllers\AssetManagement\AssetController::class, 'storeImprovement'])->name('assets.improvement.store');
        Route::post('assets/{asset}/movement', [\App\Http\Controllers\AssetManagement\AssetController::class, 'storeMovement'])->name('assets.movement.store');
    });

    Route::middleware('permission:asset_reports.visible')->group(function () {
        Route::get('/reports', [\App\Http\Controllers\AssetManagement\AssetReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-pdf', [\App\Http\Controllers\AssetManagement\AssetReportController::class, 'exportPdf'])->name('reports.export-pdf');
    });
});

// User Management Module
Route::prefix('user-management')->middleware('auth')->group(function () {
    Route::middleware('permission:users.visible')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserManagement\UserController::class);
    });
    
    Route::middleware('permission:roles.visible')->group(function () {
        Route::resource('roles', \App\Http\Controllers\UserManagement\RoleController::class);
    });
    
    Route::middleware('permission:activity_logs.visible')->group(function () {
        Route::get('activity-logs', [\App\Http\Controllers\UserManagement\ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});
