<?php

namespace App\Services\Reports;

use App\Models\Project;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\BillOfMaterial;
use App\Models\ProjectProduction;
use Illuminate\Support\Facades\DB;

class ProjectReportService
{
    public function getProjectSummary($projectId)
    {
        return Project::with([
            'projectPaymentTerms.salesPayments'
        ])->findOrFail($projectId);
    }

    public function getSalesHistory($projectId)
    {
        return SalesOrder::with('creator')->where('project_id', $projectId)->latest()->get();
    }

    public function getPurchasedMaterials($projectId)
    {
        // 1. Dari Purchase Release yang memiliki project_id
        $poIds = \App\Models\PurchaseOrder::where('project_id', $projectId)
            ->where('status', 'Approved') // Asumsi PO harus di-approve
            ->pluck('id');

        $purchased = [];
        $poDetails = \App\Models\PurchaseOrderDetail::with(['product.unit', 'purchaseOrder'])
            ->whereIn('purchase_order_id', $poIds)
            ->get();

        foreach ($poDetails as $detail) {
            $productId = $detail->product_id;
            if (!isset($purchased[$productId])) {
                $purchased[$productId] = [
                    'product_name' => $detail->product->product_name ?? '-',
                    'product_code' => $detail->product->product_code ?? '-',
                    'unit' => $detail->product->unit->unit_name ?? '-',
                    'quantity' => 0,
                    'total_cost' => 0,
                ];
            }
            $purchased[$productId]['quantity'] += $detail->quantity;
            $purchased[$productId]['total_cost'] += $detail->subtotal;
        }
        
        return array_values($purchased);
    }

    public function getMaterialUsage($projectId)
    {
        $usage = [];

        $projectProductions = ProjectProduction::with(['details.product.unit', 'details.billOfMaterial'])
            ->where('project_id', $projectId)
            ->get();

        foreach ($projectProductions as $pp) {
            foreach ($pp->details as $detail) {
                if ($detail->product) {
                    $productId = $detail->product->id;
                    if (!isset($usage[$productId])) {
                        $usage[$productId] = [
                            'product_name' => $detail->product->product_name,
                            'product_code' => $detail->product->product_code ?? '-',
                            'engineering_category' => $detail->product->engineering_category ?? 'Mechanical',
                            'unit' => $detail->product->unit->unit_name ?? 'N/A',
                            'quantity' => 0,
                            'material_cost' => 0
                        ];
                    }
                    $usage[$productId]['quantity'] += $detail->quantity;
                    $usage[$productId]['material_cost'] += $detail->material_cost;
                }
            }
        }

        return array_values($usage);
    }

    public function getServiceUsage($projectId)
    {
        $usage = [];

        // Ambil dari ProjectProductionService
        $projectProductions = ProjectProduction::with(['services'])
            ->where('project_id', $projectId)
            ->get();

        foreach ($projectProductions as $pp) {
            foreach ($pp->services as $service) {
                $name = $service->service_name;
                if (!isset($usage[$name])) {
                    $usage[$name] = [
                        'service_name' => $name,
                        'total_quantity' => 0,
                        'total_subtotal' => 0
                    ];
                }
                $usage[$name]['total_quantity'] += $service->quantity;
                $usage[$name]['total_subtotal'] += $service->subtotal;
            }
        }
        
        return array_values($usage);
    }

    public function calculateProjectMargin(Project $project)
    {
        return [
            'project_value' => $project->project_value,
            'total_hpp' => $project->hpp,
            'margin' => $project->margin,
            'margin_percentage' => $project->margin_percentage
        ];
    }
}
