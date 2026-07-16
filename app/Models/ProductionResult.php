<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class ProductionResult extends Model
{
    use Sortable;
    public $sortable = ['id', 'result_number', 'production_order_id', 'warehouse_id', 'result_date', 'quantity_target', 'quantity_finished', 'quantity_reject', 'notes', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['result_number', 'production_order_id', 'warehouse_id', 'result_date', 'quantity_target', 'quantity_finished', 'quantity_reject', 'notes'];

    public function productionOrder(): BelongsTo { return $this->belongsTo(ProductionOrder::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }

    

}