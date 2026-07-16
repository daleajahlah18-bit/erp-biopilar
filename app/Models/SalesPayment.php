<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;
class SalesPayment extends Model
{
    use Sortable;
    public $sortable = ['id', "payment_number", "sales_invoice_id", "project_payment_term_id", "payment_date", "payment_amount", "payment_method", "notes", "created_by", 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    protected $fillable = ["payment_number", "sales_invoice_id", "project_payment_term_id", "payment_date", "payment_amount", "payment_method", "notes", "created_by"];

    public function salesInvoice() { return $this->belongsTo(SalesInvoice::class); }
    public function creator() { return $this->belongsTo(User::class, "created_by"); }
    public function projectPaymentTerm() { return $this->belongsTo(ProjectPaymentTerm::class, 'project_payment_term_id'); }

    

}