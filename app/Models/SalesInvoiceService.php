<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class SalesInvoiceService extends Model
{
    use Sortable;
    public $sortable = ['id', 'sales_invoice_id',
        'service_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'sales_invoice_id',
        'service_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes'
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    

}