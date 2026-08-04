<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\EnterpriseAuditTrail;

use Kyslik\ColumnSortable\Sortable;
class ReportPhase extends Model
{
    use Sortable;
    public $sortable = ['id', 'project_id',
        'report_number',
        'document_date',
        'progress_percentage',
        'client_sign_name_1',
        'client_sign_position_1',
        'client_sign_name_2',
        'client_sign_position_2',
        'client_sign_name_3',
        'client_sign_position_3',
        'client_sign_name_4',
        'client_sign_position_4',
        'company_sign_name_1',
        'company_sign_position_1',
        'company_sign_name_2',
        'company_sign_position_2',
        'company_sign_name_3',
        'company_sign_position_3',
        'company_sign_name_4',
        'company_sign_position_4',
        'created_by', 'created_at', 'updated_at'];

    use EnterpriseAuditTrail;

    protected $fillable = [
        'project_id',
        'report_number',
        'document_date',
        'document_location',
        'progress_percentage',
        'client_sign_name_1',
        'client_sign_position_1',
        'client_sign_name_2',
        'client_sign_position_2',
        'client_sign_name_3',
        'client_sign_position_3',
        'client_sign_name_4',
        'client_sign_position_4',
        'company_sign_name_1',
        'company_sign_position_1',
        'company_sign_name_2',
        'company_sign_position_2',
        'company_sign_name_3',
        'company_sign_position_3',
        'company_sign_name_4',
        'company_sign_position_4',
        'created_by',
        'opening_paragraph',
        'progress_paragraph',
        'closing_paragraph',
        'additional_notes'
    ];

    protected $casts = [
        'document_date' => 'date',
        'progress_percentage' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
