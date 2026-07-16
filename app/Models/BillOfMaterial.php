<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Kyslik\ColumnSortable\Sortable;
class BillOfMaterial extends Model
{
    use Sortable;
    public $sortable = ['id', 'bom_number', 'bom_name', 'product_id', 'total_hpp', 'notes', 'created_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    protected $fillable = ['bom_number', 'bom_name', 'product_id', 'total_hpp', 'notes', 'created_by'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function details(): HasMany { return $this->hasMany(BillOfMaterialDetail::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    

}