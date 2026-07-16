<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class DailyReportTool extends Model
{
    use Sortable;
    public $sortable = ['id', 'daily_report_id',
        'tool_name',
        'quantity',
        'unit', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'tool_name',
        'quantity',
        'unit',
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }

    

}