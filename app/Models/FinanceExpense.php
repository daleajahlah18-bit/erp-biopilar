<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use App\Traits\EnterpriseAuditTrail;

class FinanceExpense extends Model
{
    use HasFactory, SoftDeletes, Sortable, EnterpriseAuditTrail;

    protected $fillable = [
        'expense_number',
        'project_id',
        'category_id',
        'expense_date',
        'description',
        'amount',
        'payment_method',
        'paid_to',
        'reference_number',
        'notes',
        'created_by',
        'updated_by',
    ];

    public $sortable = [
        'id',
        'expense_number',
        'expense_date',
        'amount',
        'payment_method',
        'created_at',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function category()
    {
        return $this->belongsTo(FinanceExpenseCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments()
    {
        return $this->hasMany(FinanceExpenseAttachment::class, 'finance_expense_id');
    }
}
