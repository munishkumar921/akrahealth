<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'social_history' => 'nullable|string',
            'sexually_active' => 'nullable|boolean',
            'diet' => 'nullable|string',
            'physical_activity' => 'nullable|string',
            'employment' => 'nullable|string',
            'tobacco_use' => 'nullable|boolean',
            'tobacco_use_details' => 'nullable|string',
            'alcohol_use' => 'nullable|string',
            'drug_use' => 'nullable|string',
            'illicit_drug_use' => 'nullable|string',
            'mental_health_notes' => 'nullable|string',
            'psychological_history' => 'nullable|string',
            'devolepmental_history' => 'nullable|string',
            'past_medication_trials' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sexually_active.boolean' => 'The sexually active field must be a boolean value.',
            'tobacco_use.boolean' => 'The tobacco use field must be a boolean value.',
        ];
    }
}
