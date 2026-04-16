<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['nullable', 'string'],
            'medication' => ['required', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'dosage_unit' => ['required', 'string', 'max:100'],
            'route' => ['nullable', Rule::in(['oral', 'topical', 'intravenous', 'intramuscular', 'sublingual', 'nasal', 'rectal', 'inhalation'])],
            'sig' => ['required', 'string'],
            'date_active' => ['required', 'date'],
            'date_inactive' => ['nullable', 'date'],
        ];
    }
}
