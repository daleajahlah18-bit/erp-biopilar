<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;
class Supplier extends Model
{
    use Sortable;
    public $sortable = ['id', 'supplier_name', 'supplier_address', 'supplier_phone', 'supplier_email', 'bank_account', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    protected $fillable = ['supplier_name', 'supplier_address', 'supplier_phone', 'supplier_email', 'bank_account'];

    

}