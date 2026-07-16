<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class ItemJournal extends Model
{
    use Sortable;
    public $sortable = ['id', 'journal_number', 'transaction_type', 'product_id', 'warehouse_id', 'quantity', 'description', 'reference_number', 'transaction_date', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['journal_number', 'transaction_type', 'product_id', 'warehouse_id', 'quantity', 'description', 'reference_number', 'transaction_date'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }

    

}