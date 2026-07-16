<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Kyslik\ColumnSortable\Sortable;
class ProjectProduction extends Model
{
    use Sortable;
    public $sortable = ['id', 'project_production_number',
        'project_id',
        'warehouse_id',
        'production_date',
        'notes',
        'status',
        'created_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;

    protected $fillable = [
        'project_production_number',
        'project_id',
        'warehouse_id',
        'production_date',
        'notes',
        'status',
        'created_by'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ProjectProductionDetail::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ProjectProductionService::class);
    }

    

}