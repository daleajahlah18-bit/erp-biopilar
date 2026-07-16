<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class PurchaseOrderDetail extends Model
{
    use Sortable;
    public $sortable = ['id', 'purchase_order_id', 'product_id', 'unit_id', 'quantity', 'unit_price', 'subtotal', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['purchase_order_id', 'product_id', 'unit_id', 'quantity', 'unit_price', 'subtotal'];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }

    

}