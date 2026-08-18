<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinanceExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        $totalExpense = FinanceExpense::sum('amount');
        
        $currentMonth = Carbon::now()->startOfMonth();
        $totalMonthExpense = FinanceExpense::where('expense_date', '>=', $currentMonth)->sum('amount');
        
        $latestExpenses = FinanceExpense::with(['project', 'category'])
            ->latest('expense_date')
            ->take(5)
            ->get();
            
        $expenseByCategory = FinanceExpense::join('finance_expense_categories', 'finance_expenses.category_id', '=', 'finance_expense_categories.id')
            ->select('finance_expense_categories.name as category_name', DB::raw('SUM(amount) as total'))
            ->groupBy('finance_expense_categories.name')
            ->orderByDesc('total')
            ->get();
            
        $expenseByProject = FinanceExpense::join('projects', 'finance_expenses.project_id', '=', 'projects.id')
            ->select('projects.project_name', DB::raw('SUM(amount) as total'))
            ->groupBy('projects.id', 'projects.project_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('finance.dashboard.index', compact(
            'totalExpense',
            'totalMonthExpense',
            'latestExpenses',
            'expenseByCategory',
            'expenseByProject'
        ));
    }
}
