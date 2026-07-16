<?php

// Controller
file_put_contents('app/Http/Controllers/Sales/SalesOrderController.php', '<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Customer;
use App\Models\Product;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderController extends Controller
{
    public function index()
    {
        $orders = SalesOrder::with(["customer", "creator"])->latest()->paginate(10);
        return view("sales.orders.index", compact("orders"));
    }

    public function create()
    {
        $customers = Customer::where("status", "Active")->get();
        $products = Product::with("unit")->get();
        return view("sales.orders.create", compact("customers", "products"));
    }

    public function store(Request $request, NumberGeneratorService $numGen)
    {
        $request->validate([
            "customer_id" => "required|exists:customers,id",
            "sales_order_date" => "required|date",
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

            $order = SalesOrder::create([
                "sales_order_number" => $numGen->generate("SO", SalesOrder::class, "sales_order_number"),
                "customer_id" => $request->customer_id,
                "sales_order_date" => $request->sales_order_date,
                "total_amount" => $request->total_amount,
                "notes" => $request->notes,
                "status" => "Confirmed", // Langsung Confirmed
                "created_by" => auth()->id()
            ]);

            foreach ($request->products as $item) {
                $product = Product::find($item["product_id"]);
                SalesOrderDetail::create([
                    "sales_order_id" => $order->id,
                    "product_id" => $item["product_id"],
                    "unit_id" => $product->unit_id,
                    "quantity" => $item["quantity"],
                    "unit_price" => $item["unit_price"],
                    "subtotal" => $item["subtotal"],
                ]);
            }

            DB::commit();
            return redirect()->route("sales.orders.index")->with("success", "Sales Order berhasil dibuat.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    public function show(SalesOrder $order)
    {
        $order->load(["customer", "details.product.unit", "creator"]);
        return view("sales.orders.show", compact("order"));
    }

    public function printPdf(SalesOrder $order)
    {
        $order->load(["customer", "details.product.unit", "creator"]);
        $pdf = Pdf::loadView("sales.orders.pdf", compact("order"));
        return $pdf->download($order->sales_order_number . ".pdf");
    }

    public function getProductInfo($id)
    {
        $product = Product::with("unit")->find($id);
        if (!$product) return response()->json(["error" => "Product not found"], 404);

        // Get Stock
        $stock = DB::table("stocks")
            ->where("product_id", $id)
            ->sum("quantity");

        return response()->json([
            "unit_name" => $product->unit->unit_name ?? "-",
            "unit_price" => 0, // Default 0
            "available_stock" => $stock
        ]);
    }
}
');

mkdir('resources/views/sales/orders', 0777, true);

// Views: Index
file_put_contents('resources/views/sales/orders/index.blade.php', '
@extends("layouts.app")
@section("title", "Sales Order")
@section("page_title", "Sales Order")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Sales Order</h5>
        <a href="{{ route("sales.orders.create") }}" class="btn-primary-custom px-3 py-2 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Buat Sales Order
        </a>
    </div>
    <div class="card-body">
        @if(session("success"))
            <div class="alert alert-success">{{ session("success") }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="table-light">
                    <tr>
                        <th>No Order</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->sales_order_number }}</td>
                            <td>{{ date("d/m/Y", strtotime($order->sales_order_date)) }}</td>
                            <td>{{ $order->customer->customer_name }}</td>
                            <td class="text-end">Rp {{ number_format($order->total_amount, 2, ",", ".") }}</td>
                            <td>
                                @if($order->status == "Draft") <span class="badge bg-secondary">Draft</span>
                                @elseif($order->status == "Confirmed") <span class="badge bg-primary">Confirmed</span>
                                @elseif($order->status == "Invoiced") <span class="badge bg-success">Invoiced</span>
                                @else <span class="badge bg-danger">Cancelled</span> @endif
                            </td>
                            <td>
                                <a href="{{ route("sales.orders.show", $order->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Belum ada pesanan penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
');

// Views: Create
file_put_contents('resources/views/sales/orders/create.blade.php', '
@extends("layouts.app")
@section("title", "Buat Sales Order")
@section("page_title", "Sales Order")
@section("page_subtitle", "Buat Order Baru")

@section("content")
<div class="card">
    <div class="card-body">
        @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
        <form action="{{ route("sales.orders.store") }}" method="POST" id="soForm">
            @csrf
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>No Order</label>
                    <input type="text" class="form-control bg-light" readonly placeholder="Auto Generate">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tanggal *</label>
                    <input type="date" name="sales_order_date" class="form-control" required value="{{ date("Y-m-d") }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Customer *</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Keterangan / Project</label>
                    <input type="text" name="notes" class="form-control">
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between mb-3">
                <h5 class="text-primary">Detail Produk</h5>
                <button type="button" class="btn-primary-custom py-1 px-2" id="btnAddItem"><i class="bi bi-plus"></i> Tambah Produk</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="30%">Produk</th>
                            <th width="10%">Stok</th>
                            <th width="10%">Qty</th>
                            <th width="10%">Unit</th>
                            <th width="15%">Harga (Rp)</th>
                            <th width="20%">Subtotal (Rp)</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Total Amount :</td>
                            <td class="text-end fw-bold text-primary" id="totalDisplay">0,00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" name="total_amount" id="totalInput" value="0">

            <div class="text-end mt-4">
                <button type="submit" class="btn-primary-custom px-4">Simpan Sales Order</button>
            </div>
        </form>
    </div>
</div>

<div id="productOptions" style="display:none;">
    <option value="">-- Pilih --</option>
    @foreach($products as $p)
        <option value="{{ $p->id }}">{{ $p->product_name }}</option>
    @endforeach
</div>
@endsection

@stack("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tbody = document.getElementById("itemsTbody");
    const btnAdd = document.getElementById("btnAddItem");
    const optionsHtml = document.getElementById("productOptions").innerHTML;
    let rowIndex = 0;

    function addRow() {
        const tr = document.createElement("tr");
        tr.className = "item-row";
        tr.innerHTML = `
            <td>
                <select name="products[${rowIndex}][product_id]" class="form-select product-select" required>
                    ${optionsHtml}
                </select>
            </td>
            <td class="stock-text align-middle text-center">-</td>
            <td>
                <input type="number" name="products[${rowIndex}][quantity]" class="form-control qty-input" required min="0.01" step="0.01" value="1">
            </td>
            <td class="unit-text align-middle">-</td>
            <td>
                <input type="number" name="products[${rowIndex}][unit_price]" class="form-control price-input" required min="0" value="0">
            </td>
            <td class="text-end align-middle fw-bold">
                <span class="subtotal-display">0,00</span>
                <input type="hidden" name="products[${rowIndex}][subtotal]" class="subtotal-input" value="0">
            </td>
            <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
    }

    addRow();
    btnAdd.addEventListener("click", addRow);

    tbody.addEventListener("click", function(e) {
        if(e.target.closest(".btn-remove")) {
            e.target.closest("tr").remove();
            calculateTotal();
        }
    });

    tbody.addEventListener("change", function(e) {
        if(e.target.classList.contains("product-select")) {
            const row = e.target.closest("tr");
            const pid = e.target.value;
            if(!pid) return;

            fetch(`/sales/orders/api/product-info/${pid}`)
            .then(res => res.json())
            .then(data => {
                row.querySelector(".unit-text").textContent = data.unit_name;
                row.querySelector(".stock-text").textContent = data.available_stock;
            });
        }
    });

    tbody.addEventListener("input", function(e) {
        if(e.target.classList.contains("qty-input") || e.target.classList.contains("price-input")) {
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
file_put_contents('resources/views/sales/orders/show.blade.php', '
@extends("layouts.app")
@section("title", "Detail Sales Order")
@section("page_title", "Sales Order")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Sales Order: {{ $order->sales_order_number }}</h5>
        <div>
            <a href="{{ route("sales.orders.pdf", $order->id) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf"></i> Download PDF</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Tanggal</strong></td><td>: {{ date("d/m/Y", strtotime($order->sales_order_date)) }}</td></tr>
                    <tr><td><strong>Customer</strong></td><td>: {{ $order->customer->customer_name }}</td></tr>
                    <tr><td><strong>PIC</strong></td><td>: {{ $order->customer->customer_pic ?? "-" }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="30%"><strong>Status</strong></td><td>: {{ $order->status }}</td></tr>
                    <tr><td><strong>Keterangan</strong></td><td>: {{ $order->notes ?? "-" }}</td></tr>
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
                @foreach($order->details as $d)
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
                    <td colspan="4" class="text-end fw-bold">Total:</td>
                    <td class="text-end fw-bold text-primary fs-5">Rp {{ number_format($order->total_amount, 2, ",", ".") }}</td>
                </tr>
            </tfoot>
        </table>
        <a href="{{ route("sales.orders.index") }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
@endsection
');

// Views: PDF
file_put_contents('resources/views/sales/orders/pdf.blade.php', '<!DOCTYPE html>
<html>
<head>
    <title>Sales Order {{ $order->sales_order_number }}</title>
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
        <h2>SALES ORDER</h2>
        <h3>PT BIO PILAR UTAMA</h3>
    </div>
    <table style="width:100%; margin-bottom: 20px;">
        <tr>
            <td width="50%">
                <strong>No Order:</strong> {{ $order->sales_order_number }}<br>
                <strong>Tanggal:</strong> {{ date("d/m/Y", strtotime($order->sales_order_date)) }}<br>
                <strong>Notes:</strong> {{ $order->notes }}
            </td>
            <td width="50%" style="text-align:right;">
                <strong>Kepada Yth:</strong><br>
                {{ $order->customer->customer_name }}<br>
                {{ $order->customer->customer_address }}<br>
                {{ $order->customer->customer_phone }}
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
            @foreach($order->details as $index => $item)
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
                <td class="text-end"><strong>Rp {{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
');
