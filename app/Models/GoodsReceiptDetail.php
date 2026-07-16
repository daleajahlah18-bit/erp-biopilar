<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class GoodsReceiptDetail extends Model
{
    use Sortable;
    public $sortable = ['id', 'goods_receipt_id', 'product_id', 'quantity_order', 'quantity_received', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['goods_receipt_id', 'product_id', 'quantity_order', 'quantity_received'];

    public function goodsReceipt(): BelongsTo { return $this->belongsTo(GoodsReceipt::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    

}