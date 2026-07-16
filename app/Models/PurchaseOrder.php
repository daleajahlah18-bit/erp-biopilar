<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Kyslik\ColumnSortable\Sortable;
class PurchaseOrder extends Model
{
    use Sortable;
    public $sortable = ['id', 'po_number','supplier_id','project_id','po_date','project_note','total_amount','is_ppn', 'ppn_percentage', 'ppn_amount', 'grand_total','status','created_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    protected $fillable = ['po_number','supplier_id','project_id','po_date','project_note','total_amount','is_ppn', 'ppn_percentage', 'ppn_amount', 'grand_total','status','created_by'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function details(): HasMany { return $this->hasMany(PurchaseOrderDetail::class); }
    public function goodsReceipts(): HasMany { return $this->hasMany(GoodsReceipt::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->details()->sum('subtotal')]);
    }

    

}