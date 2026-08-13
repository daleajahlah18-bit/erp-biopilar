<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSCurvePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        's_curve_item_id',
        'week_number',
        'planned_percentage'
    ];

    public function item()
    {
        return $this->belongsTo(ProjectSCurveItem::class, 's_curve_item_id');
    }
}
