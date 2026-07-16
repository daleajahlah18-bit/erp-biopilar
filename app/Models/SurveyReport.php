<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EnterpriseAuditTrail;

use Kyslik\ColumnSortable\Sortable;
class SurveyReport extends Model
{
    use Sortable;
    public $sortable = ['id', 'report_number',
        'survey_location',
        'client_name',
        'client_address',
        'pic_client',
        'phone_number',
        'survey_date',
        'surveyor',
        'opening_description',
        'closing_description',
        'created_by', 'created_at', 'updated_at'];

    use HasFactory, EnterpriseAuditTrail;

    protected $fillable = [
        'report_number',
        'survey_location',
        'client_name',
        'client_address',
        'pic_client',
        'phone_number',
        'survey_date',
        'surveyor',
        'opening_description',
        'closing_description',
        'created_by'
    ];

    protected $casts = [
        'survey_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function nodes()
    {
        return $this->hasMany(SurveyReportNode::class)->orderBy('sort_order');
    }
}
