<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSCurve extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'start_date',
        'end_date',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function items()
    {
        return $this->hasMany(ProjectSCurveItem::class, 's_curve_id');
    }

    public function actuals()
    {
        return $this->hasMany(ProjectSCurveActual::class, 's_curve_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function calculateProjectWeeks($startDate, $endDate)
    {
        $start = \Illuminate\Support\Carbon::parse($startDate)->startOfDay();
        $end = \Illuminate\Support\Carbon::parse($endDate)->startOfDay();
        $diffDays = $start->diffInDays($end);
        return max(1, (int) ceil($diffDays / 7));
    }
}
