<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    /**
     * Get product details including unit and last purchase price.
     */
    public function show($id)
    {
        $product = Product::with('unit')->findOrFail($id);

        // Find the most recent purchase for this product to get the last purchase price
        $lastPurchase = DB::table('purchase_order_details')
            ->join('purchase_orders', 'purchase_order_details.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_order_details.product_id', $product->id)
            ->whereNull('purchase_orders.deleted_at')
            ->orderBy('purchase_orders.po_date', 'desc')
            ->select('purchase_order_details.unit_price')
            ->first();

        return response()->json([
            'id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'unit_id' => $product->unit_id,
            'unit_name' => $product->unit ? $product->unit->unit_name : null,
            'last_purchase_price' => $lastPurchase ? $lastPurchase->unit_price : null,
        ]);
    }
}
