<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class SalesOrderDetail extends Model
{
    use Sortable;
    public $sortable = ['id', "sales_order_id", "product_id", "unit_id", "quantity", "unit_price", "subtotal", 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ["sales_order_id", "product_id", "unit_id", "quantity", "unit_price", "subtotal"];

    public function salesOrder() { return $this->belongsTo(SalesOrder::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function unit() { return $this->belongsTo(Unit::class); }

    

}