<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSCurveActual extends Model
{
    use HasFactory;

    protected $fillable = [
        's_curve_id',
        's_curve_item_id',
        'week_number',
        'actual_percentage',
        'updated_by'
    ];

    public function sCurve()
    {
        return $this->belongsTo(ProjectSCurve::class, 's_curve_id');
    }

    public function item()
    {
        return $this->belongsTo(ProjectSCurveItem::class, 's_curve_item_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
