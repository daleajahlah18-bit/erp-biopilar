<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Rab extends Model
{
    use HasFactory, SoftDeletes, EnterpriseAuditTrail, Sortable;

    public $sortable = [
        'id',
        'project_id',
        'rab_name',
        'total_amount',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'project_id',
        'rab_name',
        'total_amount',
        'status',
        'created_by'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function nodes()
    {
        return $this->hasMany(RabNode::class)->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
