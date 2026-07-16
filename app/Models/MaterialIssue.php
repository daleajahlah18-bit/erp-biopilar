<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Kyslik\ColumnSortable\Sortable;
class MaterialIssue extends Model
{
    use Sortable;
    public $sortable = ['id', 'issue_number', 'production_order_id', 'warehouse_id', 'issue_date', 'issued_by', 'notes', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['issue_number', 'production_order_id', 'warehouse_id', 'issue_date', 'issued_by', 'notes'];

    public function productionOrder(): BelongsTo { return $this->belongsTo(ProductionOrder::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function details(): HasMany { return $this->hasMany(MaterialIssueDetail::class); }

    

}