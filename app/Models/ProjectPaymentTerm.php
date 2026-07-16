<?php

namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;

use Kyslik\ColumnSortable\Sortable;
class ProjectPaymentTerm extends Model
{
    use Sortable;
    public $sortable = ['id', 'project_id',
        'top_type',
        'percentage',
        'term_value',
        'term_unit', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'project_id',
        'top_type',
        'percentage',
        'term_value',
        'term_unit'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function salesPayments()
    {
        return $this->hasMany(SalesPayment::class);
    }

    public function getNominalAttribute()
    {
        if (!$this->project) return 0;
        return ($this->project->project_value * $this->percentage) / 100;
    }

    public function getTotalPaidAttribute()
    {
        return $this->salesPayments()->whereNull('deleted_at')->sum('payment_amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->nominal - $this->total_paid;
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->remaining_amount <= 0) return 'Paid';
        if ($this->remaining_amount < $this->nominal) return 'Partially Paid';
        return 'Unpaid';
    }

    

}