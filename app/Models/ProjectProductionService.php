<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class ProjectProductionService extends Model
{
    use Sortable;
    public $sortable = ['id', 'project_production_id',
        'service_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use HasFactory;

    protected $fillable = [
        'project_production_id',
        'service_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes'
    ];

    public function projectProduction()
    {
        return $this->belongsTo(ProjectProduction::class);
    }

    

}