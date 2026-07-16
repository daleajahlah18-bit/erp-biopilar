<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class BillOfMaterialDetail extends Model
{
    use Sortable;
    public $sortable = ['id', 'bill_of_material_id', 'product_id', 'quantity', 'unit_id', 'unit_cost', 'subtotal', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['bill_of_material_id', 'product_id', 'quantity', 'unit_id', 'unit_cost', 'subtotal'];

    public function billOfMaterial(): BelongsTo { return $this->belongsTo(BillOfMaterial::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }

    

}