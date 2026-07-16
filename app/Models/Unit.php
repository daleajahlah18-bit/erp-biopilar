<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class Unit extends Model
{
    use Sortable;
    public $sortable = ['id', 'unit_name', 'description', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['unit_name', 'description'];

    

}