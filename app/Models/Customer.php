<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;
class Customer extends Model
{
    use Sortable;
    public $sortable = ['id', "customer_code", "customer_name", "customer_pic", "customer_phone", "customer_email", "customer_address", "payment_terms", "status", 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    protected $fillable = ["customer_code", "customer_name", "customer_pic", "customer_phone", "customer_email", "customer_address", "payment_terms", "status"];

    public function salesOrders() { return $this->hasMany(SalesOrder::class); }
    public function salesInvoices() { return $this->hasMany(SalesInvoice::class); }

    

}