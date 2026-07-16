<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;
class ProjectDocument extends Model
{
    use Sortable;
    public $sortable = ['id', 'project_id',
        'document_name',
        'document_category',
        'google_drive_file_id',
        'google_drive_folder_id',
        'google_drive_link',
        'mime_type',
        'file_size',
        'version',
        'remarks',
        'uploaded_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'document_name',
        'document_category',
        'google_drive_file_id',
        'google_drive_folder_id',
        'google_drive_link',
        'mime_type',
        'file_size',
        'version',
        'remarks',
        'uploaded_by'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    

}