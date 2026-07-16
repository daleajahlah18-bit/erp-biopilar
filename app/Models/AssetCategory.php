<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class AssetCategory extends Model
{
    use Sortable;
    public $sortable = ['id', 'category_code', 'category_name', 
        'default_useful_life_commercial', 'default_method_commercial', 
        'default_useful_life_fiscal', 'default_method_fiscal', 'default_residual_value_percent', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'category_code', 'category_name', 
        'default_useful_life_commercial', 'default_method_commercial', 
        'default_useful_life_fiscal', 'default_method_fiscal', 'default_residual_value_percent'
    ];

    

}