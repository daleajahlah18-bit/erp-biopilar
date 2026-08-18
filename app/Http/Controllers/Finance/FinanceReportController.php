<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinanceExpense;
use App\Models\Project;
use App\Models\FinanceExpenseCategory;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:finance.view')->only(['index']);
        $this->middleware('permission:finance.export')->only(['exportPdf']);
    }

    public function index(Request $request)
    {
        $projects = Project::orderBy('project_name')->get();
        $categories = FinanceExpenseCategory::where('is_active', true)->get();
        $paymentMethods = ['Cash', 'Bank Transfer', 'Debit Card', 'Credit Card', 'E-Wallet', 'Other'];

        $expenses = null;
        $totalAmount = 0;

        // If it's a search request (has filter params)
        if ($request->has('filter')) {
            $query = FinanceExpense::with(['project', 'category', 'creator'])->orderBy('expense_date', 'desc');

            if ($request->filled('date_from')) {
                $query->whereDate('expense_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('expense_date', '<=', $request->date_to);
            }
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            $expenses = $query->get();
            $totalAmount = $expenses->sum('amount');

            if ($request->has('export') && $request->export == 'pdf') {
                $pdf = Pdf::loadView('finance.reports.pdf', compact('expenses', 'totalAmount', 'request'));
                return $pdf->stream('Finance-Expense-Report.pdf');
            }
        }

        return view('finance.reports.index', compact('projects', 'categories', 'paymentMethods', 'expenses', 'totalAmount'));
    }
}
