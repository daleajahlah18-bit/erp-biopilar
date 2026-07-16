<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class DailyReportDocumentation extends Model
{
    use Sortable;
    public $sortable = ['id', 'daily_report_id',
        'photo',
        'caption', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'photo',
        'caption',
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }

    

}