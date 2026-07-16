<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class SystemMenu extends Model
{
    use Sortable;
    public $sortable = ['id', 'name', 'parent', 'created_at', 'updated_at'];

    use EnterpriseAuditTrail;
    use HasFactory;

    protected $fillable = [
        'name',
        'parent',
    ];

    

}