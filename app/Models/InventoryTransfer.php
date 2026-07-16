<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Kyslik\ColumnSortable\Sortable;
class InventoryTransfer extends Model
{
    use Sortable;
    public $sortable = ['id', 'transfer_number', 'source_warehouse_id', 'destination_warehouse_id', 'transfer_date', 'notes', 'created_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['transfer_number', 'source_warehouse_id', 'destination_warehouse_id', 'transfer_date', 'notes', 'created_by'];

    public function sourceWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'source_warehouse_id'); }
    public function destinationWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'destination_warehouse_id'); }
    public function details(): HasMany { return $this->hasMany(InventoryTransferDetail::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    

}