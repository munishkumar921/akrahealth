<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImmunizationsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function authorize()
    {
        return true; // Allow all for now
    }

    public function rules(): array
    {
        return [
            'immunization' => ['required', 'string', 'max:255'],
            'elsewhere' => ['boolean'],
            'vis' => ['boolean'],
            'dosage' => ['required', 'string', 'max:255'],
            'dosage_unit' => ['nullable', 'string', 'max:50'],
            'sequence' => ['nullable', 'string', 'max:255'],
            'route' => ['required', 'string', 'max:255'],
            'body_site' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'date' => ['required'], // e.g. MMDDYYYY format (12062025)
            'expiration' => ['required'],
            'action' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'immunization.required' => 'The immunization name is required.',
            'dosage.required' => 'Please enter the dosage.',
            'route.required' => 'Please select a route.',
            'manufacturer.required' => 'Please enter manufacturer name.',
            'date.required' => 'Please select the immunization date.',
            'date.digits' => 'The date format must be MMDDYYYY (8 digits).',
            'expiration.required' => 'Please select the expiration date.',
            'expiration.digits' => 'The expiration format must be MMDDYYYY (8 digits).',
        ];
    }
}
