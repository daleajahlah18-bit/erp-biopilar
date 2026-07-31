<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesInvoice;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\Product;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 1. Highlight Cards
        $totalSalesThisMonth = SalesInvoice::whereMonth('invoice_date', $currentMonth)
            ->whereYear('invoice_date', $currentYear)
            ->sum('total_amount');

        $totalPurchasesThisMonth = PurchaseOrder::whereMonth('po_date', $currentMonth)
            ->whereYear('po_date', $currentYear)
            ->sum('grand_total');

        $activeProjects = Project::where('project_status', 'In Progress')->count();

        // Low stock (using a subquery or join, or just a rough metric if stock is not directly in products)
        // Since products doesn't have a stock column directly, we might just count products for now or leave it as 0
        $lowStockProducts = 0; // Placeholder as actual stock requires checking 'stocks' table

        // 2. Charts Data
        
        // Sales vs Purchases (12 months trend)
        $salesTrend = [];
        $purchasesTrend = [];
        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $month = $date->month;
            $year = $date->year;
            $months[] = $date->format('M Y');

            $salesTrend[] = SalesInvoice::whereMonth('invoice_date', $month)
                ->whereYear('invoice_date', $year)
                ->sum('total_amount');

            $purchasesTrend[] = PurchaseOrder::whereMonth('po_date', $month)
                ->whereYear('po_date', $year)
                ->sum('grand_total');
        }

        // Project Status Donut Chart
        $projectStatuses = Project::selectRaw('project_status, count(*) as count')
            ->groupBy('project_status')
            ->pluck('count', 'project_status')
            ->toArray();

        // 3. Recent Activities
        try {
            $recentActivities = ActivityLog::with('causer')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $recentActivities = collect();
        }

        return view('dashboard', compact(
            'totalSalesThisMonth',
            'totalPurchasesThisMonth',
            'activeProjects',
            'lowStockProducts',
            'salesTrend',
            'purchasesTrend',
            'months',
            'projectStatuses',
            'recentActivities'
        ));
    }
}
