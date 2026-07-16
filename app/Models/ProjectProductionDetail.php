<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class ProjectProductionDetail extends Model
{
    use Sortable;
    public $sortable = ['id', 'project_production_id',
        'bill_of_material_id',
        'product_id',
        'quantity',
        'unit_id',
        'stock_before',
        'stock_after',
        'last_purchase_price',
        'bom_hpp',
        'material_cost', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'project_production_id',
        'bill_of_material_id',
        'product_id',
        'quantity',
        'unit_id',
        'stock_before',
        'stock_after',
        'last_purchase_price',
        'bom_hpp',
        'material_cost'
    ];

    public function projectProduction(): BelongsTo
    {
        return $this->belongsTo(ProjectProduction::class);
    }

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class, 'bill_of_material_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    

}