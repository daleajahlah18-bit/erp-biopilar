<?php

// 1. Customer Model
file_put_contents('app/Models/Customer.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = ["customer_code", "customer_name", "customer_pic", "customer_phone", "customer_email", "customer_address", "payment_terms", "status"];

    public function salesOrders() { return $this->hasMany(SalesOrder::class); }
    public function salesInvoices() { return $this->hasMany(SalesInvoice::class); }
}');

// 2. SalesOrder Model
file_put_contents('app/Models/SalesOrder.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use SoftDeletes;
    protected $fillable = ["sales_order_number", "customer_id", "sales_order_date", "notes", "total_amount", "status", "created_by"];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function details() { return $this->hasMany(SalesOrderDetail::class); }
    public function invoice() { return $this->hasOne(SalesInvoice::class); }
    public function creator() { return $this->belongsTo(User::class, "created_by"); }
}');

// 3. SalesOrderDetail Model
file_put_contents('app/Models/SalesOrderDetail.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SalesOrderDetail extends Model
{
    protected $fillable = ["sales_order_id", "product_id", "unit_id", "quantity", "unit_price", "subtotal"];

    public function salesOrder() { return $this->belongsTo(SalesOrder::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
}');

// 4. SalesInvoice Model
file_put_contents('app/Models/SalesInvoice.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoice extends Model
{
    use SoftDeletes;
    protected $fillable = ["invoice_number", "sales_order_id", "invoice_date", "total_amount", "notes", "status", "payment_status", "terms_of_payment_days", "created_by"];

    public function salesOrder() { return $this->belongsTo(SalesOrder::class); }
    public function details() { return $this->hasMany(SalesInvoiceDetail::class); }
    public function payments() { return $this->hasMany(SalesPayment::class); }
    public function creator() { return $this->belongsTo(User::class, "created_by"); }
    public function customer() { return $this->salesOrder->customer(); }
}');

// 5. SalesInvoiceDetail Model
file_put_contents('app/Models/SalesInvoiceDetail.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceDetail extends Model
{
    protected $fillable = ["sales_invoice_id", "product_id", "unit_id", "quantity", "unit_price", "subtotal"];

    public function salesInvoice() { return $this->belongsTo(SalesInvoice::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
}');

// 6. SalesPayment Model
file_put_contents('app/Models/SalesPayment.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesPayment extends Model
{
    use SoftDeletes;
    protected $fillable = ["payment_number", "sales_invoice_id", "payment_date", "payment_amount", "payment_method", "notes", "created_by"];

    public function salesInvoice() { return $this->belongsTo(SalesInvoice::class); }
    public function creator() { return $this->belongsTo(User::class, "created_by"); }
}');
