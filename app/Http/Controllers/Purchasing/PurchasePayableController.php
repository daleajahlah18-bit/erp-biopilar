<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchasePayableController extends Controller
{
    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder.supplier'])
            ->whereIn('payment_status', ['Unpaid', 'Partially Paid', 'Paid']); // Or just omit this to show all

        // Filter by Supplier
        if ($request->filled('supplier_id')) {
            $query->whereHas('purchaseOrder', function($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        // Filter by Status (Unpaid, Partially Paid, Paid, Overdue)
        if ($request->filled('status')) {
            if ($request->status === 'Overdue') {
                $query->where('remaining_amount', '>', 0)
                      ->where('due_date', '<', now()->toDateString());
            } else {
                $query->where('payment_status', $request->status);
                // Pastikan tidak termasuk Overdue jika yg direquest Unpaid/Partially Paid
                if (in_array($request->status, ['Unpaid', 'Partially Paid'])) {
                    $query->where(function($q) {
                        $q->whereNull('due_date')
                          ->orWhere('due_date', '>=', now()->toDateString());
                    });
                }
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('receipt_date', [$request->start_date, $request->end_date]);
        }

        $payables = $query->sortable()->latest()->sortable()->paginate(10);
        $suppliers = Supplier::all();

        // Dashboard Aggregation
        // Ambil semua data (tanpa pagination) utk sum
        $allPayables = GoodsReceipt::where('remaining_amount', '>', 0)->get();
        
        $totalHutang = $allPayables->sum('remaining_amount');
        
        $hutangJatuhTempo = $allPayables->filter(function($gr) {
            return $gr->due_date && $gr->due_date < now()->toDateString();
        })->sum('remaining_amount');
        
        $hutangBelumJatuhTempo = $allPayables->filter(function($gr) {
            return !$gr->due_date || $gr->due_date >= now()->toDateString();
        })->sum('remaining_amount');

        $jumlahSupplierNunggak = $allPayables->pluck('purchaseOrder.supplier_id')->unique()->count();

        return view('purchasing.payables.index', compact(
            'payables', 'suppliers', 'totalHutang', 'hutangJatuhTempo', 'hutangBelumJatuhTempo', 'jumlahSupplierNunggak'
        ));
    }

    public function show(GoodsReceipt $goods_receipt)
    {
        $goods_receipt->load(['purchaseOrder.supplier', 'payments', 'details.product.unit']);
        return view('purchasing.payables.show', compact('goods_receipt'));
    }
}
