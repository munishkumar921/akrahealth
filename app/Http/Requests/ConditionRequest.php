<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConditionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['nullable', 'string'],
            'issue' => ['required', 'string'],
            'rcopia_sync' => ['nullable', 'string'],
            'type' => ['required', 'string'],
            'reconcile' => ['nullable', 'string'],
            'notes' => ['required', 'string'],
            'date_active' => ['date'],
            'date_inactive' => ['nullable'],
        ];
    }
}
