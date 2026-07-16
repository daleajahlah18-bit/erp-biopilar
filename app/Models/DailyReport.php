<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class DailyReport extends Model
{
    use Sortable;
    public $sortable = ['id', 'project_id',
        'report_number',
        'report_date',
        'weather',
        'work_description',
        'evaluation_notes',
        'created_by',
        'updated_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use HasFactory;

    protected $fillable = [
        'project_id',
        'report_number',
        'report_date',
        'weather',
        'work_description',
        'evaluation_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function manpower()
    {
        return $this->hasMany(DailyReportManpower::class);
    }

    public function materials()
    {
        return $this->hasMany(DailyReportMaterial::class);
    }

    public function tools()
    {
        return $this->hasMany(DailyReportTool::class);
    }

    public function documentations()
    {
        return $this->hasMany(DailyReportDocumentation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    

}