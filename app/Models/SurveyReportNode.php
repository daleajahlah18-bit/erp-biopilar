<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class SurveyReportNode extends Model
{
    use Sortable;
    public $sortable = ['id', 'survey_report_id',
        'parent_id',
        'title',
        'node_type',
        'qty',
        'remark',
        'sort_order', 'created_at', 'updated_at'];

    use HasFactory;

    protected $fillable = [
        'survey_report_id',
        'parent_id',
        'title',
        'node_type',
        'qty',
        'remark',
        'sort_order'
    ];

    public function report()
    {
        return $this->belongsTo(SurveyReport::class, 'survey_report_id');
    }

    public function parent()
    {
        return $this->belongsTo(SurveyReportNode::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SurveyReportNode::class, 'parent_id')->orderBy('sort_order');
    }

    public function attachments()
    {
        return $this->hasMany(SurveyReportAttachment::class)->orderBy('sort_order');
    }
}
