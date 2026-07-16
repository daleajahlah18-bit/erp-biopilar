<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class InventoryTransferDetail extends Model
{
    use Sortable;
    public $sortable = ['id', 'inventory_transfer_id', 'product_id', 'quantity', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['inventory_transfer_id', 'product_id', 'quantity'];

    public function inventoryTransfer(): BelongsTo { return $this->belongsTo(InventoryTransfer::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    

}