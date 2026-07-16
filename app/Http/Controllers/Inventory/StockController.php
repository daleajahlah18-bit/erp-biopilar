<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request) 
    { 
        $warehouse_id = $request->input('warehouse_id');
        $search = $request->input('search');
        
        $stocks = Stock::with(['product.unit', 'warehouse'])
            ->when($warehouse_id, function($query) use ($warehouse_id) {
                return $query->where('warehouse_id', $warehouse_id);
            })
            ->when($search, function($query) use ($search) {
                return $query->whereHas('product', function($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('product_code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->sortable()->paginate(15)->withQueryString(); 
            
        $warehouses = \App\Models\Warehouse::all();
            
        return view('inventory.stocks.index', compact('stocks', 'warehouses', 'warehouse_id', 'search')); 
    }
}