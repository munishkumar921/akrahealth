<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'date' => ['required', 'date'],
            'to' => ['nullable', 'array'],
            'to.*' => ['nullable', 'exists:users,id'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['nullable', 'exists:users,id'],
        ];
    }
}
