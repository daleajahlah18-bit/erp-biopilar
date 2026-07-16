<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kyslik\ColumnSortable\Sortable;
class MaterialIssueDetail extends Model
{
    use Sortable;
    public $sortable = ['id', 'material_issue_id', 'raw_material_id', 'quantity_required', 'quantity_issued', 'unit_id', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = ['material_issue_id', 'raw_material_id', 'quantity_required', 'quantity_issued', 'unit_id'];

    public function materialIssue(): BelongsTo { return $this->belongsTo(MaterialIssue::class); }
    public function rawMaterial(): BelongsTo { return $this->belongsTo(Product::class, 'raw_material_id'); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }

    

}