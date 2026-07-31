<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesReceivableController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\ProjectPaymentTerm::with(['project.salesOrders.invoice', 'salesPayments']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $allTerms = $query->get();

        $terms = collect();

        $totalProjectValue = 0;
        $totalTerminValue = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
        $terminPaid = 0;
        $terminUnpaid = 0;
        
        $processedProjects = [];

        $topOrder = [
            'Down Payment' => 1,
            'Material On Site' => 2,
            'After Progress 25%' => 3,
            'After Progress 50%' => 4,
            'After Progress 60%' => 5,
            'After Installation' => 6,
            'Monthly Progress' => 7,
            'After Progress 100%' => 8,
            'Retention' => 9
        ];

        foreach ($allTerms as $term) {
            $project = $term->project;
            if (!$project) continue;

            // Find an invoice to base the due date on
            $invoice = null;
            foreach ($project->salesOrders as $so) {
                if ($so->invoice && in_array($so->invoice->status, ['Approved', 'Paid'])) {
                    $invoice = $so->invoice;
                    break;
                }
            }

            if (!$invoice) continue; // Only show terms if there's an invoice

            if (!in_array($project->id, $processedProjects)) {
                $totalProjectValue += $project->project_value;
                $processedProjects[] = $project->id;
            }

            $dueDate = Carbon::parse($invoice->invoice_date);
            if (strtolower($term->term_unit) == 'days') {
                $dueDate->addDays($term->term_value);
            } else {
                $dueDate->addMonths($term->term_value);
            }

            $nominal = $term->nominal;
            $paid = $term->total_paid;
            $remaining = $term->remaining_amount;
            $status = $term->payment_status;

            $totalTerminValue += $nominal;
            $totalPaid += $paid;
            $totalOutstanding += $remaining;

            if ($status == 'Paid') {
                $terminPaid++;
            } else {
                $terminUnpaid++;
            }

            $topSeq = $topOrder[$term->top_type] ?? 99;

            $terms->push((object)[
                'project_name' => $project->project_name,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date,
                'top_type' => $term->top_type,
                'top_seq' => $topSeq,
                'percentage' => $term->percentage,
                'nominal' => $nominal,
                'total_paid' => $paid,
                'remaining_amount' => $remaining,
                'status' => $status,
                'due_date' => $dueDate
            ]);
        }

        // Sort terms
        $terms = $terms->sortBy([
            ['project_name', 'asc'],
            ['invoice_date', 'asc'],
            ['top_seq', 'asc']
        ])->values();

        // Pagination
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = $terms->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedTerms = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems, $terms->count(), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'query' => $request->query()
        ]);

        $summary = [
            "total_project_value" => $totalProjectValue,
            "total_termin_value" => $totalTerminValue,
            "total_paid" => $totalPaid,
            "total_outstanding" => $totalOutstanding,
            "termin_paid" => $terminPaid,
            "termin_unpaid" => $terminUnpaid
        ];
        
        $projects = \App\Models\Project::orderBy('project_name')->get();

        return view("sales.receivables.index", compact("paginatedTerms", "summary", "projects"));
    }

    public function show(SalesInvoice $invoice)
    {
        $invoice->load(["salesOrder.customer", "salesOrder.project", "payments.projectPaymentTerm"]);
        return view("sales.receivables.show", compact("invoice"));
    }
}
