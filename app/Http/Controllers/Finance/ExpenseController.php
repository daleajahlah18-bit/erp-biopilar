<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinanceExpense;
use App\Models\FinanceExpenseCategory;
use App\Models\FinanceExpenseAttachment;
use App\Models\Project;
use App\Services\NumberGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:finance.view')->only(['index', 'show']);
        $this->middleware('permission:finance.create')->only(['create', 'store']);
        $this->middleware('permission:finance.edit')->only(['edit', 'update']);
        $this->middleware('permission:finance.delete')->only(['destroy']);
        $this->middleware('permission:finance.print')->only(['exportPdf']);
    }

    public function index(Request $request)
    {
        $query = FinanceExpense::with(['project', 'category', 'creator'])->sortable()->latest('expense_date');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('expense_number', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('paid_to', 'like', "%{$q}%")
                    ->orWhere('reference_number', 'like', "%{$q}%");
            });
        }

        $expenses = $query->paginate(10)->withQueryString();
        $categories = FinanceExpenseCategory::where('is_active', true)->get();
        $projects = Project::orderBy('project_name')->get();
        
        return view('finance.expenses.index', compact('expenses', 'categories', 'projects'));
    }

    public function create()
    {
        $categories = FinanceExpenseCategory::where('is_active', true)->get();
        $paymentMethods = ['Cash', 'Bank Transfer', 'Debit Card', 'Credit Card', 'E-Wallet', 'Other'];
        return view('finance.expenses.create', compact('categories', 'paymentMethods'));
    }

    public function searchProjects(Request $request)
    {
        $q = trim($request->input('q', ''));
        
        $query = Project::query()
            ->select(['id', 'contract_number as project_number', 'project_name', 'client_name']);

        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('contract_number', 'like', "%{$q}%")
                    ->orWhere('project_name', 'like', "%{$q}%")
                    ->orWhere('client_name', 'like', "%{$q}%");
            });
        }

        $results = $query->limit(20)->get()->map(function($project) {
            return [
                'id' => $project->id,
                'text' => ($project->project_number ? '[' . $project->project_number . '] ' : '') . $project->project_name . ' - ' . $project->client_name
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function store(Request $request, NumberGeneratorService $numberGenerator)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:finance_expense_categories,id',
            'expense_date' => 'required|date',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'paid_to' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:5120', // 5MB max
        ]);

        DB::beginTransaction();
        try {
            $expense = FinanceExpense::create([
                'expense_number' => $numberGenerator->generate('EXP', FinanceExpense::class, 'expense_number'),
                'project_id' => $request->project_id,
                'category_id' => $request->category_id,
                'expense_date' => $request->expense_date,
                'description' => $request->description,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'paid_to' => $request->paid_to,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('finance_expenses', 'public');
                    FinanceExpenseAttachment::create([
                        'finance_expense_id' => $expense->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('finance.expenses.index')->with('success', 'Expense created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create expense: ' . $e->getMessage())->withInput();
        }
    }

    public function show(FinanceExpense $expense)
    {
        $expense->load(['project', 'category', 'creator', 'updater', 'attachments.uploader']);
        return view('finance.expenses.show', compact('expense'));
    }

    public function edit(FinanceExpense $expense)
    {
        $expense->load('project');
        $categories = FinanceExpenseCategory::where('is_active', true)->get();
        $paymentMethods = ['Cash', 'Bank Transfer', 'Debit Card', 'Credit Card', 'E-Wallet', 'Other'];
        return view('finance.expenses.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    public function update(Request $request, FinanceExpense $expense)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:finance_expense_categories,id',
            'expense_date' => 'required|date',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'paid_to' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $expense->update([
                'project_id' => $request->project_id,
                'category_id' => $request->category_id,
                'expense_date' => $request->expense_date,
                'description' => $request->description,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'paid_to' => $request->paid_to,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'updated_by' => auth()->id(),
            ]);

            if ($request->has('remove_attachments')) {
                foreach ($request->remove_attachments as $attachmentId) {
                    $attachment = FinanceExpenseAttachment::where('id', $attachmentId)
                        ->where('finance_expense_id', $expense->id)
                        ->first();
                    if ($attachment) {
                        // Keep file physically for audit trail if preferred, or delete:
                        // Storage::disk('public')->delete($attachment->file_path);
                        $attachment->delete();
                    }
                }
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('finance_expenses', 'public');
                    FinanceExpenseAttachment::create([
                        'finance_expense_id' => $expense->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('finance.expenses.index')->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update expense: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(FinanceExpense $expense)
    {
        $number = $expense->expense_number;
        
        DB::beginTransaction();
        try {
            $expense->delete(); // Soft delete
            DB::commit();

            return redirect()->route('finance.expenses.index')->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete expense.');
        }
    }

    public function exportPdf(FinanceExpense $expense)
    {
        $expense->load(['project', 'category', 'creator']);
        
        $pdf = Pdf::loadView('finance.expenses.pdf', compact('expense'));
        return $pdf->stream('Expense-' . $expense->expense_number . '.pdf');
    }

    public function downloadAttachment(FinanceExpense $expense, FinanceExpenseAttachment $attachment)
    {
        if ($attachment->finance_expense_id !== $expense->id) {
            abort(404);
        }
        
        $path = storage_path('app/public/' . $attachment->file_path);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->download($path, $attachment->file_name);
    }
}
