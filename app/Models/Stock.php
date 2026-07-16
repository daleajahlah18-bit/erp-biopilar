<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class Stock extends Model
{
    use Sortable;
    public $sortable = ['id', 'product_id', 'warehouse_id', 'quantity', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }

    

}