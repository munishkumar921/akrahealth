<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineManageRequest extends FormRequest
{
    protected array $dosageForms = [
        'tablet',
        'capsule',
        'syrup',
        'injection',
        'ointment',
        'spray',
        'drop',
        'powder',
        'gel',
    ];

    protected array $routes = [
        'oral',
        'topical',
        'intravenous',
        'intramuscular',
        'sublingual',
        'nasal',
        'rectal',
        'inhalation',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_prescription_required' => $this->boolean('is_prescription_required'),
            'is_encrypted' => $this->has('is_encrypted') ? $this->boolean('is_encrypted') : false,
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'exists:medicines,id'],
            'name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'composition' => ['nullable', 'string'],
            'dosage_form' => ['nullable', Rule::in($this->dosageForms)],
            'strength' => ['nullable', 'string', 'max:255'],
            'route' => ['nullable', Rule::in($this->routes)],
            'indications' => ['nullable', 'string'],
            'contraindications' => ['nullable', 'string'],
            'side_effects' => ['nullable', 'string'],
            'precautions' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'batch_no' => ['nullable', 'string', 'max:255'],
            'is_prescription_required' => ['nullable', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'is_encrypted' => ['required', 'boolean'],
        ];
    }
}
