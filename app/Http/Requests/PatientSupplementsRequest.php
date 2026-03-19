<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientSupplementsRequest extends FormRequest
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
            'supplement' => ['required', 'string'],
            'dosage' => ['nullable', 'string'],
            'dosage_unit' => ['required', 'string'],
            'route' => ['nullable', 'string'],
            'sig' => ['required', 'string'],
            'date_active' => ['date', 'string'],
            'date_inactive' => ['nullable', 'string'],
        ];
    }
}
