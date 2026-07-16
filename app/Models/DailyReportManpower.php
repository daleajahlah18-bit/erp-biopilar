<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class DailyReportManpower extends Model
{
    use Sortable;
    public $sortable = ['id', 'daily_report_id',
        'position',
        'quantity', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use HasFactory;

    protected $table = 'daily_report_manpower';

    protected $fillable = [
        'daily_report_id',
        'position',
        'quantity',
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }

    

}