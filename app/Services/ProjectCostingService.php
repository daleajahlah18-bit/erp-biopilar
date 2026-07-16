<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectCostingService
{
    /**
     * Recalculate Project Value, HPP, and Margin for a given project.
     * Must be called whenever there's a change in Sales Orders,
     * Project Productions, or Project Production Services.
     */
    public function recalculateProject(Project $project)
    {
        // 1. Project Value is now input manually, do NOT calculate from Sales Orders
        $projectValue = $project->project_value;

        // 2. Calculate Total Material Used (from Project Production Details)
        $materialCost = DB::table('project_production_details')
            ->join('project_productions', 'project_production_details.project_production_id', '=', 'project_productions.id')
            ->where('project_productions.project_id', $project->id)
            ->whereNull('project_productions.deleted_at')
            ->sum('project_production_details.material_cost');

        // 3. Calculate Total Service Cost (from Project Production Services)
        $serviceCost = DB::table('project_production_services')
            ->join('project_productions', 'project_production_services.project_production_id', '=', 'project_productions.id')
            ->where('project_productions.project_id', $project->id)
            ->whereNull('project_productions.deleted_at')
            ->sum('project_production_services.subtotal');

        // 4. Update Project HPP & Margin
        $hpp = $materialCost + $serviceCost;
        $margin = $projectValue - $hpp;
        $marginPercentage = ($projectValue > 0) ? (($margin / $projectValue) * 100) : 0;

        $project->update([
            'hpp' => $hpp,
            'margin' => $margin,
            'margin_percentage' => $marginPercentage,
        ]);
    }
}
