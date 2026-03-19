<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllergiesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['nullable'],
            'doctor_id' => ['nullable', 'string'],
            'date_active' => ['nullable', 'string'],
            'date_inactive' => ['nullable', 'string'],
            'allergies_medicine' => ['nullable', 'string', 'string'],
            'allergies_reaction' => ['nullable', 'string'],
            'allergies_severity' => ['nullable', 'string'],
            'notes' => ['nullable'],
            'rcopia_sync' => ['nullable'],
            'medicine_ndcid' => ['nullable', 'string'],
            'reconcile' => ['nullable', 'string'],
            'date_active' => ['date', 'string'],
            'date_expiration' => ['nullable', 'string'],
        ];
    }
}
