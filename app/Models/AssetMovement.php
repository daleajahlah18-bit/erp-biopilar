<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class AssetMovement extends Model
{
    use Sortable;
    public $sortable = ['id', 'asset_id', 'movement_date', 'from_department', 'to_department', 
        'from_location', 'to_location', 'from_pic', 'to_pic', 'notes', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'asset_id', 'movement_date', 'from_department', 'to_department', 
        'from_location', 'to_location', 'from_pic', 'to_pic', 'notes'
    ];
    public function asset() { return $this->belongsTo(Asset::class); }

    

}