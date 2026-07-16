<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class StockOpnameDetail extends Model
{
    use Sortable;
    public $sortable = ['id', 'stock_opname_id', 'product_id', 'system_stock', 'physical_stock', 'difference', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['stock_opname_id', 'product_id', 'system_stock', 'physical_stock', 'difference'];

    public function stockOpname(): BelongsTo { return $this->belongsTo(StockOpname::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    

}