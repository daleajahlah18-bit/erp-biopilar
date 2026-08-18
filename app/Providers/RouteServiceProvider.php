<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Get the appropriate landing page for the authenticated user based on permissions.
     */
    public static function getLandingPage()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return self::HOME;
        }

        if ($user->can('dashboard.visible')) {
            return self::HOME;
        }

        $landingPageMap = [
            's_curve.visible' => '/s-curves',
            'products.visible' => '/master/products',
            'suppliers.visible' => '/master/suppliers',
            'customers.visible' => '/master/customers',
            'units.visible' => '/master/units',
            'projects.visible' => '/master/projects',
            'warehouses.visible' => '/master/warehouses',
            'purchase_order.visible' => '/purchasing/purchase-orders',
            'goods_receipt.visible' => '/purchasing/goods-receipts',
            'purchase_payment.visible' => '/purchasing/payments',
            'accounts_payable.visible' => '/purchasing/payables',
            'bill_of_material.visible' => '/production/bom',
            'production_order.visible' => '/production/orders',
            'project_production.visible' => '/production/project-productions',
            'sales_order.visible' => '/sales/orders',
            'sales_invoice.visible' => '/sales/invoices',
            'sales_payment.visible' => '/sales/payments',
            'accounts_receivable.visible' => '/sales/receivables',
            'project_report.visible' => '/project-reports',
            'daily_report.visible' => '/project-reports/daily-reports',
            'report_phase.visible' => '/project-reports/report-phases',
            'survey_report.visible' => '/project-reports/survey-reports',
            'rab.visible' => '/project-reports/rabs',
            'product_stock.visible' => '/inventory/stocks',
            'stock_transfer.visible' => '/inventory/transfers',
            'stock_opname.visible' => '/inventory/stock-opnames',
            'item_journal.visible' => '/inventory/item-journals',
            'asset_dashboard.visible' => '/asset-management',
            'master_categories.visible' => '/asset-management/categories',
            'master_assets.visible' => '/asset-management/assets',
            'asset_reports.visible' => '/asset-management/reports',
            'users.visible' => '/user-management/users',
            'roles.visible' => '/user-management/roles',
            'activity_logs.visible' => '/user-management/activity-logs',
        ];

        foreach ($landingPageMap as $permission => $url) {
            if ($user->can($permission)) {
                return $url;
            }
        }

        return '/'; // Fallback
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . $request->ip());
        });
    }
}
