<?php

// Controller
file_put_contents('app/Http/Controllers/Sales/SalesInvoiceController.php', '<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\SalesOrder;
use App\Models\Product;
use App\Models\ItemJournal;
use App\Services\NumberGeneratorService;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesInvoiceController extends Controller
{
    public function index()
    {
        $invoices = SalesInvoice::with(["salesOrder.customer", "creator"])->latest()->paginate(10);
        return view("sales.invoices.index", compact("invoices"));
    }

    public function create()
    {
        $orders = SalesOrder::where("status", "Confirmed")->get();
        return view("sales.invoices.create", compact("orders"));
    }

    public function store(Request $request, NumberGeneratorService $numGen)
    {
        $request->validate([
            "sales_order_id" => "required|exists:sales_orders,id",
            "invoice_date" => "required|date",
            "terms_of_payment_days" => "required|integer|min:0",
            "notes" => "nullable|string",
            "products" => "required|array|min:1",
            "products.*.product_id" => "required|exists:products,id|distinct",
            "products.*.quantity" => "required|numeric|min:0.01",
            "products.*.unit_price" => "required|numeric|min:0",
            "products.*.subtotal" => "required|numeric|min:0",
            "total_amount" => "required|numeric|min:0",
        ]);

        try {
            DB::beginTransaction();

            $invoice = SalesInvoice::create([
                "invoice_number" => $numGen->generate("INV", SalesInvoice::class, "invoice_number"),
                "sales_order_id" => $request->sales_order_id,
                "invoice_date" => $request->invoice_date,
                "terms_of_payment_days" => $request->terms_of_payment_days,
                "total_amount" => $request->total_amount,
                "notes" => $request->notes,
                "status" => "Draft", // Status default
                "payment_status" => "Unpaid",
                "created_by" => auth()->id()
            ]);

            foreach ($request->products as $item) {
                $product = Product::find($item["product_id"]);
                SalesInvoiceDetail::create([
                    "sales_invoice_id" => $invoice->id,
                    "product_id" => $item["product_id"],
                    "unit_id" => $product->unit_id,
                    "quantity" => $item["quantity"],
                    "unit_price" => $item["unit_price"],
                    "subtotal" => $item["subtotal"],
                ]);
            }

            // Update SO Status
            $so = SalesOrder::find($request->sales_order_id);
            $so->update(["status" => "Invoiced"]);

            DB::commit();
            return redirect()->route("sales.invoices.index")->with("success", "Sales Invoice berhasil disimpan (Draft).");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    public function show(SalesInvoice $invoice)
    {
        $invoice->load(["salesOrder.customer", "details.product.unit", "creator"]);
        return view("sales.invoices.show", compact("invoice"));
    }

    public function approve(SalesInvoice $invoice, StockMovementService $stockService)
    {
        if ($invoice->status !== "Draft") {
            return back()->with("error", "Hanya invoice draft yang bisa di-approve.");
        }

        try {
            DB::beginTransaction();

            $invoice->load("details");

            // 1. Cek ketersediaan stok terlebih dahulu
            foreach ($invoice->details as $detail) {
                $stock = DB::table("stocks")
                    ->where("product_id", $detail->product_id)
                    ->lockForUpdate()
                    ->sum("quantity");
                
                if ($stock < $detail->quantity) {
                    throw new \Exception("Stok produk tidak mencukupi untuk melakukan Invoice. Produk ID: " . $detail->product_id);
                }
            }

            // 2. Jika aman, potong stok & buat Item Journal
            foreach ($invoice->details as $detail) {
                // Gunakan Gudang pertama yang ada stoknya (Sederhananya kita kurangi dari stok yang ada)
                $stocks = \App\Models\Stock::where("product_id", $detail->product_id)
                                           ->where("quantity", ">", 0)
                                           ->lockForUpdate()
                                           ->get();
                
                $remainingQty = $detail->quantity;

                foreach ($stocks as $stockRow) {
                    if ($remainingQty <= 0) break;
                    
                    $deduct = min($stockRow->quantity, $remainingQty);
                    
                    $stockService->out(
                        $detail->product_id,
                        $stockRow->warehouse_id,
                        $deduct,
                        "Pengurangan stok untuk Invoice " . $invoice->invoice_number
                    );
                    
                    // Catat Journal
                    ItemJournal::create([
                        "transaction_date" => now(),
                        "transaction_type" => "STOCK_OUT",
                        "reference_number" => $invoice->invoice_number,
                        "product_id" => $detail->product_id,
                        "warehouse_id" => $stockRow->warehouse_id,
                        "quantity" => $deduct,
                        "notes" => "Sales Invoice",
                        "created_by" => auth()->id()
                    ]);

                    $remainingQty -= $deduct;
                }

                if ($remainingQty > 0) {
                     throw new \Exception("Gagal memotong stok untuk Produk ID: " . $detail->product_id);
                }
            }

            // 3. Update status invoice
            $invoice->update(["status" => "Approved"]);

            DB::commit();
            return back()->with("success", "Invoice berhasil di-Approve. Stok telah dipotong.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", $e->getMessage());
        }
    }

    public function printPdf(SalesInvoice $invoice)
    {
        $invoice->load(["salesOrder.customer", "details.product.unit", "creator"]);
        $pdf = Pdf::loadView("sales.invoices.pdf", compact("invoice"));
        return $pdf->download($invoice->invoice_number . ".pdf");
    }

    public function getOrderDetails($id)
    {
        $order = SalesOrder::with(["details.product.unit", "customer"])->find($id);
        if (!$order) return response()->json(["error" => "Order not found"], 404);

        return response()->json([
            "customer" => $order->customer,
            "details" => $order->details->map(function($d) {
                // Get Stock
                $stock = DB::table("stocks")->where("product_id", $d->product_id)->sum("quantity");
                return [
                    "product_id" => $d->product_id,
                    "product_name" => $d->product->product_name,
                    "unit_name" => $d->unit->unit_name,
                    "quantity" => $d->quantity,
                    "unit_price" => $d->unit_price,
                    "available_stock" => $stock
                ];
            })
        ]);
    }
}
');

mkdir('resources/views/sales/invoices', 0777, true);

// Views: Index
file_put_contents('resources/views/sales/invoices/index.blade.php', '
@extends("layouts.app")
@section("title", "Sales Invoice")
@section("page_title", "Sales Invoice")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Sales Invoice</h5>
        <a href="{{ route("sales.invoices.create") }}" class="btn-primary-custom px-3 py-2 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Buat Invoice
        </a>
    </div>
    <div class="card-body">
        @if(session("success")) <div class="alert alert-success">{{ session("success") }}</div> @endif
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
        
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="table-light">
                    <tr>
                        <th>No Invoice</th>
                        <th>SO Ref</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->salesOrder->sales_order_number }}</td>
                            <td>{{ date("d/m/Y", strtotime($inv->invoice_date)) }}</td>
                            <td>{{ $inv->salesOrder->customer->customer_name }}</td>
                            <td class="text-end">Rp {{ number_format($inv->total_amount, 2, ",", ".") }}</td>
                            <td>
                                @if($inv->status == "Draft") <span class="badge bg-secondary">Draft</span>
                                @elseif($inv->status == "Approved") <span class="badge bg-primary">Approved</span>
                                @else <span class="badge bg-success">{{ $inv->status }}</span> @endif
                            </td>
                            <td>
                                @if($inv->payment_status == "Unpaid") <span class="badge bg-danger">Unpaid</span>
                                @elseif($inv->payment_status == "Partially Paid") <span class="badge bg-warning text-dark">Partial</span>
                                @else <span class="badge bg-success">Paid</span> @endif
                            </td>
                            <td>
                                <a href="{{ route("sales.invoices.show", $inv->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">Belum ada invoice penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection
');

// Views: Create
file_put_contents('resources/views/sales/invoices/create.blade.php', '
@extends("layouts.app")
@section("title", "Buat Sales Invoice")
@section("page_title", "Sales Invoice")
@section("page_subtitle", "Buat Invoice Baru")

@section("content")
<div class="card">
    <div class="card-body">
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
        <form action="{{ route("sales.invoices.store") }}" method="POST" id="invForm">
            @csrf
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>No Invoice</label>
                    <input type="text" class="form-control bg-light" readonly placeholder="Auto Generate">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tanggal Invoice *</label>
                    <input type="date" name="invoice_date" class="form-control" required value="{{ date("Y-m-d") }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Sales Order *</label>
                    <select name="sales_order_id" id="sales_order_id" class="form-select" required>
                        <option value="">-- Pilih SO --</option>
                        @foreach($orders as $o)
                            <option value="{{ $o->id }}">{{ $o->sales_order_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Terms (Hari) *</label>
                    <input type="number" name="terms_of_payment_days" id="terms_of_payment_days" class="form-control" required min="0" value="0">
                </div>
                <div class="col-md-12 mb-3">
                    <label>Customer</label>
                    <input type="text" id="customer_name" class="form-control bg-light" readonly>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Keterangan</label>
                    <input type="text" name="notes" class="form-control">
                </div>
            </div>

            <hr>

            <h5 class="text-primary mb-3">Detail Produk</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="30%">Produk</th>
                            <th width="10%">Stok</th>
                            <th width="15%">Qty</th>
                            <th width="10%">Unit</th>
                            <th width="15%">Harga (Rp)</th>
                            <th width="20%">Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        <tr><td colspan="6" class="text-center text-muted">Silakan pilih Sales Order terlebih dahulu.</td></tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Total Amount :</td>
                            <td class="text-end fw-bold text-primary" id="totalDisplay">0,00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" name="total_amount" id="totalInput" value="0">

            <div class="text-end mt-4">
                <button type="submit" class="btn-primary-custom px-4">Simpan sebagai Draft</button>
            </div>
        </form>
    </div>
</div>
@endsection

@stack("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const soSelect = document.getElementById("sales_order_id");
    const tbody = document.getElementById("itemsTbody");

    soSelect.addEventListener("change", function() {
        const soId = this.value;
        if(!soId) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Silakan pilih Sales Order terlebih dahulu.</td></tr>`;
            document.getElementById("customer_name").value = "";
            document.getElementById("terms_of_payment_days").value = 0;
            return;
        }

        fetch(`/sales/invoices/api/order-details/${soId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById("customer_name").value = data.customer.customer_name;
            document.getElementById("terms_of_payment_days").value = data.customer.payment_terms;
            
            tbody.innerHTML = "";
            data.details.forEach((item, index) => {
                const tr = document.createElement("tr");
                const stockWarning = item.quantity > item.available_stock ? 
                    `<br><span class="badge bg-danger">Stok Kurang!</span>` : "";

                tr.innerHTML = `
                    <td>
                        <input type="hidden" name="products[${index}][product_id]" value="${item.product_id}">
                        ${item.product_name}
                    </td>
                    <td class="text-center">
                        ${item.available_stock}
                        ${stockWarning}
                    </td>
                    <td>
                        <input type="number" name="products[${index}][quantity]" class="form-control qty-input" required min="0.01" step="0.01" value="${item.quantity}" readonly>
                    </td>
                    <td class="align-middle">${item.unit_name}</td>
                    <td>
                        <input type="number" name="products[${index}][unit_price]" class="form-control price-input" required min="0" value="${item.unit_price}">
                        <small class="text-muted">Bisa diedit</small>
                    </td>
                    <td class="text-end align-middle fw-bold">
                        <span class="subtotal-display">${parseFloat(item.quantity * item.unit_price).toLocaleString("id-ID", {minimumFractionDigits:2})}</span>
                        <input type="hidden" name="products[${index}][subtotal]" class="subtotal-input" value="${item.quantity * item.unit_price}">
                    </td>
                `;
                tbody.appendChild(tr);
            });
            calculateTotal();
        });
    });

    tbody.addEventListener("input", function(e) {
        if(e.target.classList.contains("price-input")) {
            const row = e.target.closest("tr");
            const qty = parseFloat(row.querySelector(".qty-input").value) || 0;
            const price = parseFloat(row.querySelector(".price-input").value) || 0;
            const sub = qty * price;
            
            row.querySelector(".subtotal-input").value = sub;
            row.querySelector(".subtotal-display").textContent = sub.toLocaleString("id-ID", {minimumFractionDigits:2});
            calculateTotal();
        }
    });

    function calculateTotal() {
        let tot = 0;
        document.querySelectorAll(".subtotal-input").forEach(i => tot += parseFloat(i.value) || 0);
        document.getElementById("totalInput").value = tot;
        document.getElementById("totalDisplay").textContent = tot.toLocaleString("id-ID", {minimumFractionDigits:2});
    }
});
</script>
');

// Views: Show
file_put_contents('resources/views/sales/invoices/show.blade.php', '
@extends("layouts.app")
@section("title", "Detail Sales Invoice")
@section("page_title", "Sales Invoice")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Invoice: {{ $invoice->invoice_number }}</h5>
        <div class="d-flex gap-2">
            @if($invoice->status == "Draft")
                <form action="{{ route("sales.invoices.approve", $invoice->id) }}" method="POST" onsubmit="return confirm(\'Approve invoice ini? Stok akan dipotong permanen.\')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Approve & Stock Out</button>
                </form>
            @endif
            <a href="{{ route("sales.invoices.pdf", $invoice->id) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf"></i> Download PDF</a>
        </div>
    </div>
    <div class="card-body">
        @if(session("success")) <div class="alert alert-success">{{ session("success") }}</div> @endif
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif

        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Tanggal</strong></td><td>: {{ date("d/m/Y", strtotime($invoice->invoice_date)) }}</td></tr>
                    <tr><td><strong>Jatuh Tempo</strong></td><td>: {{ date("d/m/Y", strtotime($invoice->invoice_date . " + " . $invoice->terms_of_payment_days . " days")) }}</td></tr>
                    <tr><td><strong>Customer</strong></td><td>: {{ $invoice->salesOrder->customer->customer_name }}</td></tr>
                    <tr><td><strong>SO Ref</strong></td><td>: <a href="{{ route("sales.orders.show", $invoice->salesOrder->id) }}">{{ $invoice->salesOrder->sales_order_number }}</a></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Status Inv</strong></td><td>: 
                        @if($invoice->status == "Draft") <span class="badge bg-secondary">Draft</span>
                        @elseif($invoice->status == "Approved") <span class="badge bg-primary">Approved</span>
                        @else <span class="badge bg-success">{{ $invoice->status }}</span> @endif
                    </td></tr>
                    <tr><td><strong>Pembayaran</strong></td><td>: 
                        @if($invoice->payment_status == "Unpaid") <span class="badge bg-danger">Unpaid</span>
                        @elseif($invoice->payment_status == "Partially Paid") <span class="badge bg-warning text-dark">Partial</span>
                        @else <span class="badge bg-success">Paid</span> @endif
                    </td></tr>
                    <tr><td><strong>Keterangan</strong></td><td>: {{ $invoice->notes ?? "-" }}</td></tr>
                </table>
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Harga (Rp)</th>
                    <th class="text-end">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $d)
                <tr>
                    <td>{{ $d->product->product_name }}</td>
                    <td>{{ number_format($d->quantity, 2, ",", ".") }}</td>
                    <td>{{ $d->unit->unit_name }}</td>
                    <td class="text-end">{{ number_format($d->unit_price, 2, ",", ".") }}</td>
                    <td class="text-end fw-bold">{{ number_format($d->subtotal, 2, ",", ".") }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total Tagihan:</td>
                    <td class="text-end fw-bold text-primary fs-5">Rp {{ number_format($invoice->total_amount, 2, ",", ".") }}</td>
                </tr>
            </tfoot>
        </table>
        <a href="{{ route("sales.invoices.index") }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
@endsection
');

// Views: PDF
file_put_contents('resources/views/sales/invoices/pdf.blade.php', '<!DOCTYPE html>
<html>
<head>
    <title>Sales Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 5px; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>INVOICE</h2>
        <h3>PT BIO PILAR UTAMA</h3>
    </div>
    <table style="width:100%; margin-bottom: 20px;">
        <tr>
            <td width="50%">
                <strong>No Invoice:</strong> {{ $invoice->invoice_number }}<br>
                <strong>No SO:</strong> {{ $invoice->salesOrder->sales_order_number }}<br>
                <strong>Tanggal:</strong> {{ date("d/m/Y", strtotime($invoice->invoice_date)) }}<br>
                <strong>Jatuh Tempo:</strong> {{ date("d/m/Y", strtotime($invoice->invoice_date . " + " . $invoice->terms_of_payment_days . " days")) }}
            </td>
            <td width="50%" style="text-align:right;">
                <strong>Kepada Yth:</strong><br>
                {{ $invoice->salesOrder->customer->customer_name }}<br>
                {{ $invoice->salesOrder->customer->customer_address }}<br>
                {{ $invoice->salesOrder->customer->customer_phone }}
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->details as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->product_name }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ $item->unit->unit_name }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Total Amount</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($invoice->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
');
