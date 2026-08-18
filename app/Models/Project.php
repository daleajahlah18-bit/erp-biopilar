<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kyslik\ColumnSortable\Sortable;
class Project extends Model
{
    use Sortable;
    public $sortable = ['id', 'project_name', 
        'project_address', 
        'person_in_charge',
        'client_name',
        'client_po_date',
        'project_value',
        'hpp',
        'margin',
        'margin_percentage',
        'project_status',
        'google_drive_folder_id',
        'project_start_date',
        'project_end_date',
        'client_logo',
        'field_of_work',
        'work_package',
        'client_po_number',
        'client_user_name',
        'executor_name',
        'contract_number', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    use SoftDeletes;
    
    protected $fillable = [
        'project_name', 
        'project_address', 
        'person_in_charge',
        'client_name',
        'client_po_date',
        'project_value',
        'hpp',
        'margin',
        'margin_percentage',
        'project_status',
        'google_drive_folder_id',
        'project_start_date',
        'project_end_date',
        'client_logo',
        'field_of_work',
        'work_package',
        'client_po_number',
        'client_user_name',
        'executor_name',
        'contract_number'
    ];

    protected $casts = [
        'client_po_date' => 'date',
        'project_start_date' => 'date',
        'project_end_date' => 'date',
        'project_value' => 'decimal:2',
        'hpp' => 'decimal:2',
        'margin' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function projectProductions()
    {
        return $this->hasMany(ProjectProduction::class);
    }

    public function projectPaymentTerms()
    {
        return $this->hasMany(ProjectPaymentTerm::class);
    }

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function reportPhases()
    {
        return $this->hasMany(ReportPhase::class);
    }

    public function rabs()
    {
        return $this->hasMany(Rab::class);
    }

    public function financeExpenses()
    {
        return $this->hasMany(FinanceExpense::class, 'project_id');
    }
}