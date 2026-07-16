<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class SalesOrderService extends Model
{
    use Sortable;
    public $sortable = ['id', 'sales_order_id',
        'service_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'sales_order_id',
        'service_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes'
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    

}