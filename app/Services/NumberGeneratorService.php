<?php

namespace App\Services;

class NumberGeneratorService
{
    public function generate(string $prefix, string $model, string $field): string
    {
        $date     = now()->format('Ymd');
        $fullPref = "{$prefix}-{$date}-";
        $count    = app($model)::where($field, 'like', "{$fullPref}%")
                               ->lockForUpdate()->count();
        return $fullPref . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
