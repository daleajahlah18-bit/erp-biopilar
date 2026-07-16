<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class AssetMaintenance extends Model
{
    use Sortable;
    public $sortable = ['id', 'asset_id', 'maintenance_date', 'maintenance_type', 'vendor', 'description', 'cost', 'document_link', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'asset_id', 'maintenance_date', 'maintenance_type', 'vendor', 'description', 'cost', 'document_link'
    ];
    public function asset() { return $this->belongsTo(Asset::class); }

    

}