<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'project_address' => 'nullable|string',
            'person_in_charge' => 'required|string|max:255',
            'client_po_date' => 'nullable|date',
            'project_value' => 'required|numeric|min:1',
            'project_status' => 'required|in:Draft,On Going,Completed,Cancelled',
            'project_start_date' => 'nullable|date',
            'project_end_date' => 'nullable|date|after_or_equal:project_start_date',
            'payment_terms' => 'required|array|min:1',
            'payment_terms.*.top_type' => 'required|string',
            'payment_terms.*.percentage' => 'required|numeric|min:0.01|max:100',
            'payment_terms.*.term_value' => 'required|integer|min:0',
            'payment_terms.*.term_unit' => 'required|in:Days,Months',
            
            // Project Identity for Daily Reports
            'client_logo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'field_of_work' => 'nullable|string|max:255',
            'work_package' => 'nullable|string|max:255',
            'client_user_name' => 'nullable|string|max:255',
            'executor_name' => 'nullable|string|max:255',
            'contract_number' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $paymentTerms = $this->input('payment_terms', []);
            if (is_array($paymentTerms) && count($paymentTerms) > 0) {
                $totalPercentage = 0;
                foreach ($paymentTerms as $term) {
                    $totalPercentage += floatval($term['percentage'] ?? 0);
                }
                
                // Use a small epsilon for float comparison just in case, but exactly 100 is required
                if (abs($totalPercentage - 100) > 0.01) {
                    $validator->errors()->add('payment_terms', 'Total persentase Terms of Payment harus 100%.');
                }
            }
        });
    }
}
