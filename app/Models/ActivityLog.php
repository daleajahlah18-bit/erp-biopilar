<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;
use Kyslik\ColumnSortable\Sortable;

class ActivityLog extends Activity
{
    use Sortable;
    public $sortable = ['id', 'log_name', 'description', 'event', 'created_at', 'updated_at', 'ip_address', 'user_agent'];
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($activity) {
            $activity->ip_address = request()->ip();
            $activity->user_agent = request()->userAgent();
            $activity->url = request()->fullUrl();
        });
    }
}
