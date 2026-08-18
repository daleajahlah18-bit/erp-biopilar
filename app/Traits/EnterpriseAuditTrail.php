<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Str;
use Spatie\Activitylog\Contracts\Activity;

trait EnterpriseAuditTrail
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $moduleName = $this->getAuditModuleName();
        $documentIdentifier = $this->getAuditDocumentIdentifier();

        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) use ($moduleName, $documentIdentifier) {
                return ucfirst($eventName) . " {$moduleName} {$documentIdentifier}";
            });
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        // Intercept before saving to resolve readable values for old/new changes
        if (isset($activity->properties['attributes'])) {
            $properties = $activity->properties->toArray();
            
            $resolvedAttributes = [];
            $resolvedOld = [];
            
            foreach ($properties['attributes'] as $key => $value) {
                $resolvedAttributes[$key] = [
                    'label' => $this->getAuditReadableLabel($key),
                    'value' => $this->resolveAuditRelationshipValue($key, $value)
                ];
            }
            
            if (isset($properties['old'])) {
                foreach ($properties['old'] as $key => $value) {
                    $resolvedOld[$key] = $this->resolveAuditRelationshipValue($key, $value);
                }
            }
            
            $properties['resolved'] = [
                'attributes' => $resolvedAttributes,
                'old' => $resolvedOld
            ];
            
            $activity->properties = collect($properties);
        }
    }

    protected function getAuditModuleName(): string
    {
        return Str::title(Str::snake(class_basename($this), ' '));
    }

    protected function getAuditDocumentIdentifier(): string
    {
        $possibleColumns = [
            'po_number', 'invoice_number', 'report_number', 'payment_number', 
            'order_number', 'code', 'employee_id', 'name', 'title', 'expense_number'
        ];

        foreach ($possibleColumns as $column) {
            if ($this->getAttribute($column)) {
                return $this->getAttribute($column);
            }
        }

        return '#' . $this->getKey();
    }

    protected function getAuditReadableLabel($key): string
    {
        if (str_ends_with($key, '_id')) {
            $key = substr($key, 0, -3);
        }
        
        $customLabels = [
            'po_number' => 'Purchase Release Number',
            'qty' => 'Quantity',
            'po_date' => 'PR Date',
            'grand_total' => 'Grand Total',
            'is_ppn' => 'Has PPN',
            'ppn_percentage' => 'PPN Percentage',
            'ppn_amount' => 'PPN Amount',
            'total_amount' => 'Total Amount'
        ];

        if (isset($customLabels[$key])) {
            return $customLabels[$key];
        }

        return Str::title(str_replace('_', ' ', $key));
    }

    protected function resolveAuditRelationshipValue($key, $value)
    {
        if (is_null($value)) return '-';
        if (is_bool($value)) return $value ? 'Yes' : 'No';

        // Check if this looks like a relationship field
        if (str_ends_with($key, '_id')) {
            $relationName = Str::camel(substr($key, 0, -3));
            
            // Check if the relation method exists on the model
            if (method_exists($this, $relationName)) {
                try {
                    $relatedModel = $this->$relationName()->getRelated();
                    $record = $relatedModel->find($value);
                    
                    if ($record) {
                        // Try to find a descriptive field in the related model
                        $displayFields = ['name', 'title', 'company_name', 'po_number', 'code', 'employee_id'];
                        foreach ($displayFields as $field) {
                            if (isset($record->$field)) {
                                return $record->$field;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Fallback to raw value on error
                }
            }
        }
        
        // Format numeric values if they look like large amounts
        if (is_numeric($value) && $value > 9999 && !str_ends_with($key, 'year') && !str_contains($key, 'number')) {
            // Very simple heuristic to detect money/large numbers
            if (str_contains($key, 'amount') || str_contains($key, 'price') || str_contains($key, 'total') || str_contains($key, 'value')) {
                return 'Rp ' . number_format((float)$value, 0, ',', '.');
            }
        }

        return $value;
    }
}
