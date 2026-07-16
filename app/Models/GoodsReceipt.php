<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Kyslik\ColumnSortable\Sortable;
class GoodsReceipt extends Model
{
    use Sortable;
    public $sortable = ['id', 'gr_number',
        'purchase_order_id',
        'warehouse_id',
        'received_by',
        'receipt_date',
        'created_by',
        'total_amount',
        'payment_status',
        'total_paid',
        'remaining_amount',
        'terms_of_payment_days',
        'due_date', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use HasFactory;

    protected $fillable = [
        'gr_number',
        'purchase_order_id',
        'warehouse_id',
        'received_by',
        'receipt_date',
        'created_by',
        'total_amount',
        'payment_status',
        'total_paid',
        'remaining_amount',
        'terms_of_payment_days',
        'due_date'
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(GoodsReceiptDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    

}