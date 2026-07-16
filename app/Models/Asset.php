<?php
namespace App\Models;

use App\Traits\EnterpriseAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use Kyslik\ColumnSortable\Sortable;
class Asset extends Model
{
    use Sortable;
    public $sortable = ['id', 'asset_code', 'asset_name', 'category_id', 'brand', 'model', 'serial_number', 'asset_description',
        'location', 'department', 'responsible_person',
        'purchase_date', 'start_depreciation_date', 'acquisition_cost', 'residual_value',
        'commercial_method', 'commercial_useful_life', 'fiscal_method', 'fiscal_useful_life',
        'status', 'vendor', 'invoice_number', 'notes',
        'purchase_invoice_doc', 'warranty_doc', 'photo_doc', 'manual_doc', 'created_at', 'updated_at'];


    use EnterpriseAuditTrail;
    protected $fillable = [
        'asset_code', 'asset_name', 'category_id', 'brand', 'model', 'serial_number', 'asset_description',
        'location', 'department', 'responsible_person',
        'purchase_date', 'start_depreciation_date', 'acquisition_cost', 'residual_value',
        'commercial_method', 'commercial_useful_life', 'fiscal_method', 'fiscal_useful_life',
        'status', 'vendor', 'invoice_number', 'notes',
        'purchase_invoice_doc', 'warranty_doc', 'photo_doc', 'manual_doc'
    ];
    
    public function category() { return $this->belongsTo(AssetCategory::class); }
    public function maintenances() { return $this->hasMany(AssetMaintenance::class); }
    public function improvements() { return $this->hasMany(AssetImprovement::class); }
    public function movements() { return $this->hasMany(AssetMovement::class); }

    /**
     * Engine: Calculate the complete dynamic depreciation schedule up to current month.
     */
    private function calculateDynamicSchedule($bookType)
    {
        $schedule = [];
        $startDate = Carbon::parse($this->start_depreciation_date)->startOfMonth();
        $currentDate = Carbon::now()->startOfMonth();
        
        // If depreciation hasn't started yet
        if ($currentDate->lt($startDate)) {
            return $schedule;
        }

        $usefulLife = $bookType == 'Commercial' ? $this->commercial_useful_life : $this->fiscal_useful_life;
        $method = $bookType == 'Commercial' ? $this->commercial_method : $this->fiscal_method;
        $residualValue = $this->residual_value;
        
        $monthsPassed = 0;
        $currentBookValue = $this->acquisition_cost;
        $accumulated = 0;

        // Group improvements by period 'Y-m'
        $improvements = $this->improvements->groupBy(function ($item) {
            return Carbon::parse($item->improvement_date)->format('Y-m');
        });

        $loopDate = $startDate->copy();

        while ($monthsPassed < $usefulLife && $loopDate->lte($currentDate)) {
            $period = $loopDate->format('Y-m');
            
            // Check if there are improvements this month
            $capexAmount = 0;
            if (isset($improvements[$period])) {
                foreach ($improvements[$period] as $imp) {
                    $capexAmount += $imp->improvement_cost;
                }
                $currentBookValue += $capexAmount;
            }

            $remainingLife = $usefulLife - $monthsPassed;
            $beginningBookValue = $currentBookValue;
            $expense = 0;
            $methodUsed = $method;

            if ($method == 'Straight Line') {
                $expense = ($currentBookValue - $residualValue) / $remainingLife;
            } else if ($method == 'Double Declining Balance') {
                $rate = (1 / ($usefulLife / 12)) * 2; 
                $expense = ($currentBookValue * $rate) / 12;

                // Switch to Straight Line if SL is greater
                $slExpense = ($currentBookValue - $residualValue) / $remainingLife;
                if ($slExpense > $expense) {
                    $expense = $slExpense;
                    $methodUsed = 'Straight Line (Switched)';
                }
            }
            
            // Ensure we don't depreciate below residual value
            if ($currentBookValue - $expense < $residualValue) {
                $expense = $currentBookValue - $residualValue;
            }

            // Final month adjustment
            if ($monthsPassed + 1 == $usefulLife) {
                $expense = $currentBookValue - $residualValue;
            }

            if ($expense < 0) $expense = 0;

            $accumulated += $expense;
            $currentBookValue -= $expense;
            
            // Prevent floating point tiny values near 0
            if ($currentBookValue <= $residualValue + 0.01) {
                $currentBookValue = $residualValue;
            }

            $schedule[] = [
                'period' => $period,
                'beginning_book_value' => $beginningBookValue,
                'capex' => $capexAmount,
                'expense' => $expense,
                'accumulated_depreciation' => $accumulated,
                'ending_book_value' => $currentBookValue,
                'method_used' => $methodUsed
            ];

            $monthsPassed++;
            $loopDate->addMonth();
        }

        return $schedule;
    }

    // ========== COMMERCIAL ATTRIBUTES ==========

    public function getCommercialScheduleAttribute()
    {
        return $this->calculateDynamicSchedule('Commercial');
    }

    public function getCommercialElapsedMonthsAttribute()
    {
        return count($this->commercial_schedule);
    }

    public function getCommercialMonthlyDepreciationAttribute()
    {
        $schedule = $this->commercial_schedule;
        return count($schedule) > 0 ? end($schedule)['expense'] : 0;
    }

    public function getCommercialAccumulatedDepreciationAttribute()
    {
        $schedule = $this->commercial_schedule;
        return count($schedule) > 0 ? end($schedule)['accumulated_depreciation'] : 0;
    }

    public function getCommercialBookValueAttribute()
    {
        $schedule = $this->commercial_schedule;
        return count($schedule) > 0 ? end($schedule)['ending_book_value'] : $this->acquisition_cost;
    }

    public function getCommercialRemainingLifeAttribute()
    {
        return $this->commercial_useful_life - $this->commercial_elapsed_months;
    }

    // ========== FISCAL ATTRIBUTES ==========

    public function getFiscalScheduleAttribute()
    {
        return $this->calculateDynamicSchedule('Fiscal');
    }

    public function getFiscalElapsedMonthsAttribute()
    {
        return count($this->fiscal_schedule);
    }

    public function getFiscalMonthlyDepreciationAttribute()
    {
        $schedule = $this->fiscal_schedule;
        return count($schedule) > 0 ? end($schedule)['expense'] : 0;
    }

    public function getFiscalAccumulatedDepreciationAttribute()
    {
        $schedule = $this->fiscal_schedule;
        return count($schedule) > 0 ? end($schedule)['accumulated_depreciation'] : 0;
    }

    public function getFiscalBookValueAttribute()
    {
        $schedule = $this->fiscal_schedule;
        return count($schedule) > 0 ? end($schedule)['ending_book_value'] : $this->acquisition_cost;
    }

    public function getFiscalRemainingLifeAttribute()
    {
        return $this->fiscal_useful_life - $this->fiscal_elapsed_months;
    }

    

}