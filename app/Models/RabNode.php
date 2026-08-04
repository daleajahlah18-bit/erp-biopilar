<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class RabNode extends Model
{
    use HasFactory, Sortable;

    public $sortable = [
        'id',
        'rab_id',
        'parent_id',
        'node_type',
        'title',
        'sort_order',
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'rab_id',
        'parent_id',
        'node_type',
        'title',
        'specification',
        'qty',
        'unit',
        'unit_price',
        'total_price',
        'sort_order'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function parent()
    {
        return $this->belongsTo(RabNode::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(RabNode::class, 'parent_id')->orderBy('sort_order');
    }
}
