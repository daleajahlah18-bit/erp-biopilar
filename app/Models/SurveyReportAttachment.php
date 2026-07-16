<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class SurveyReportAttachment extends Model
{
    use Sortable;
    public $sortable = ['id', 'survey_report_node_id',
        'file_path',
        'caption',
        'sort_order', 'created_at', 'updated_at'];

    use HasFactory;

    protected $fillable = [
        'survey_report_node_id',
        'file_path',
        'caption',
        'sort_order'
    ];

    public function node()
    {
        return $this->belongsTo(SurveyReportNode::class, 'survey_report_node_id');
    }
}
