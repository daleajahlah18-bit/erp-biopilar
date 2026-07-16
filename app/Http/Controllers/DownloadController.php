<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function download($module, $id, $field)
    {
        $mappings = [
            'projects' => ['model' => \App\Models\Project::class, 'permission' => 'projects.visible'],
            'users' => ['model' => \App\Models\User::class, 'permission' => 'users.visible'],
            'daily_reports' => ['model' => \App\Models\DailyReportDocumentation::class, 'permission' => 'daily_report.visible'],
            'assets' => ['model' => \App\Models\Asset::class, 'permission' => 'master_assets.visible'],
            'asset_maintenances' => ['model' => \App\Models\AssetMaintenance::class, 'permission' => 'master_assets.visible']
        ];

        if (!array_key_exists($module, $mappings)) {
            abort(404);
        }

        // Verify Authentication and Authorization
        $this->authorize($mappings[$module]['permission']);

        // Verify Record Existence
        $modelClass = $mappings[$module]['model'];
        $record = $modelClass::findOrFail($id);

        $path = $record->{$field};

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($path);
    }

    public function display($module, $id, $field)
    {
        $mappings = [
            'projects' => ['model' => \App\Models\Project::class, 'permission' => 'projects.visible'],
            'users' => ['model' => \App\Models\User::class, 'permission' => 'users.visible'],
            'daily_reports' => ['model' => \App\Models\DailyReportDocumentation::class, 'permission' => 'daily_report.visible'],
            'assets' => ['model' => \App\Models\Asset::class, 'permission' => 'master_assets.visible'],
            'asset_maintenances' => ['model' => \App\Models\AssetMaintenance::class, 'permission' => 'master_assets.visible']
        ];

        if (!array_key_exists($module, $mappings)) {
            abort(404);
        }

        // Verify Authentication and Authorization
        $this->authorize($mappings[$module]['permission']);

        // Verify Record Existence
        $modelClass = $mappings[$module]['model'];
        $record = $modelClass::findOrFail($id);

        $path = $record->{$field};

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->response($path);
    }
}
