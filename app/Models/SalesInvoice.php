<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;
class SalesInvoice extends Model
{
    use Sortable;
    public $sortable = ['id', "invoice_number", "sales_order_id", "invoice_date", "total_amount", "notes", "status", "payment_status", "terms_of_payment_days", "created_by", 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    protected $fillable = ["invoice_number", "sales_order_id", "invoice_date", "total_amount", "notes", "status", "payment_status", "terms_of_payment_days", "created_by"];

    public function salesOrder() { return $this->belongsTo(SalesOrder::class); }
    public function details() { return $this->hasMany(SalesInvoiceDetail::class); }
    public function services() { return $this->hasMany(SalesInvoiceService::class); }
    public function payments() { return $this->hasMany(SalesPayment::class); }
    public function creator() { return $this->belongsTo(User::class, "created_by"); }
    public function customer() { return $this->salesOrder->customer(); }

    

}