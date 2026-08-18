<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Traits\EnterpriseAuditTrail;

class FinanceExpenseCategory extends Model
{
    use HasFactory, Sortable, EnterpriseAuditTrail;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    public $sortable = [
        'id',
        'code',
        'name',
        'is_active',
        'created_at',
    ];

    public function expenses()
    {
        return $this->hasMany(FinanceExpense::class, 'category_id');
    }
}
