<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class ProductionOrderDetail extends Model
{
    use Sortable;
    public $sortable = ['id', 'production_order_id', 
        'product_id', 
        'quantity_per_bom', 
        'quantity_required', 
        'stock_available', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'production_order_id', 
        'product_id', 
        'quantity_per_bom', 
        'quantity_required', 
        'stock_available'
    ];

    public function productionOrder(): BelongsTo 
    { 
        return $this->belongsTo(ProductionOrder::class); 
    }
    
    public function product(): BelongsTo 
    { 
        return $this->belongsTo(Product::class, 'product_id'); 
    }

    

}