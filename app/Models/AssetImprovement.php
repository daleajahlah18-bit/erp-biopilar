<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Spatie\Activitylog\LogOptions;use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class AssetImprovement extends Model
{
    use Sortable;
    public $sortable = ['id', 'asset_id', 'improvement_date', 'improvement_cost', 'description', 'invoice_number', 'vendor',
        'previous_book_value_commercial', 'new_book_value_commercial', 'previous_book_value_fiscal', 'new_book_value_fiscal', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'asset_id', 'improvement_date', 'improvement_cost', 'description', 'invoice_number', 'vendor',
        'previous_book_value_commercial', 'new_book_value_commercial', 'previous_book_value_fiscal', 'new_book_value_fiscal'
    ];
    public function asset() { return $this->belongsTo(Asset::class); }

    

}