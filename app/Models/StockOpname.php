<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Kyslik\ColumnSortable\Sortable;
class StockOpname extends Model
{
    use Sortable;
    public $sortable = ['id', 'opname_number', 'warehouse_id', 'opname_date', 'notes', 'created_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['opname_number', 'warehouse_id', 'opname_date', 'notes', 'created_by'];

    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function details(): HasMany { return $this->hasMany(StockOpnameDetail::class); }

    

}