<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Kyslik\ColumnSortable\Sortable;
class ProductionOrder extends Model
{
    use Sortable;
    public $sortable = ['id', 'production_number',
        'bill_of_material_id',
        'warehouse_id',
        'target_quantity',
        'actual_quantity',
        'production_date',
        'status',
        'notes',
        'production_result_notes',
        'created_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;

    protected $fillable = [
        'production_number',
        'bill_of_material_id',
        'warehouse_id',
        'target_quantity',
        'actual_quantity',
        'production_date',
        'status',
        'notes',
        'production_result_notes',
        'created_by'
    ];

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ProductionOrderDetail::class);
    }

    

}