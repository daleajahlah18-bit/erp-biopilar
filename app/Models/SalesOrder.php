<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;
class SalesOrder extends Model
{
    use Sortable;
    public $sortable = ['id', "sales_order_number", "customer_id", "project_id", "sales_order_date", "notes", "total_amount", "status", "created_by", 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    protected $fillable = ["sales_order_number", "customer_id", "project_id", "sales_order_date", "notes", "total_amount", "status", "created_by"];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function details() { return $this->hasMany(SalesOrderDetail::class); }
    public function services() { return $this->hasMany(SalesOrderService::class); }
    public function invoice() { return $this->hasOne(SalesInvoice::class); }
    public function creator() { return $this->belongsTo(User::class, "created_by"); }

    

}