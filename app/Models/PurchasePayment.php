<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;
class PurchasePayment extends Model
{
    use Sortable;
    public $sortable = ['id', 'payment_number',
        'goods_receipt_id',
        'payment_date',
        'payment_amount',
        'payment_method',
        'notes',
        'created_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'goods_receipt_id',
        'payment_date',
        'payment_amount',
        'payment_method',
        'notes',
        'created_by'
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    

}