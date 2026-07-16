<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class AssetDepreciation extends Model
{
    use Sortable;
    public $sortable = ['id', 'asset_id', 'book_type', 'period', 'expense', 'accumulated_depreciation', 'book_value', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'asset_id', 'book_type', 'period', 'expense', 'accumulated_depreciation', 'book_value'
    ];
    public function asset() { return $this->belongsTo(Asset::class); }

    

}