<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSCurveItem extends Model
{
    use HasFactory;

    protected $fillable = [
        's_curve_id',
        'parent_id',
        'work_code',
        'work_name',
        'weight_percentage',
        'sort_order'
    ];

    public function sCurve()
    {
        return $this->belongsTo(ProjectSCurve::class, 's_curve_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProjectSCurveItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProjectSCurveItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function plans()
    {
        return $this->hasMany(ProjectSCurvePlan::class, 's_curve_item_id');
    }

    public function actuals()
    {
        return $this->hasMany(ProjectSCurveActual::class, 's_curve_item_id');
    }
}
