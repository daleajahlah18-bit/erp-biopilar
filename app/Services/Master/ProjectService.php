<?php

namespace App\Services\Master;

use App\Models\Project;

class ProjectService
{
    /**
     * Calculate HPP for a project based on related transactions.
     * Currently it is manually inputted, but structured here for future use.
     * 
     * @param Project $project
     * @return float
     */
    public function calculateHpp(Project $project)
    {
        // TODO: In the future, calculate from:
        // - Purchasing
        // - Production
        // - Project Production
        // - Inventory Usage
        // - Material Consumption

        // For now, return the manually inputted HPP or 0
        return $project->hpp ?? 0;
    }
}
