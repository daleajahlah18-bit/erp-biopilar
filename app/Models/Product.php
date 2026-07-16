<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class Product extends Model
{
    use Sortable;
    public $sortable = ['id', 'product_code', 'product_name', 'product_type', 'engineering_category', 'unit_id', 'description', 'created_by', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    protected $fillable = ['product_code', 'product_name', 'product_type', 'engineering_category', 'unit_id', 'description', 'created_by'];

    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    

}