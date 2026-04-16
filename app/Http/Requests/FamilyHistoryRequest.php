<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FamilyHistoryRequest extends FormRequest
{
    protected array $relationshipOptions = [
        'Father',
        'Mother',
        'Brother',
        'Sister',
        'Son',
        'Daughter',
        'Spouse',
        'Partner',
        'Paternal Uncle',
        'Paternal Aunt',
        'Maternal Uncle',
        'Maternal Aunt',
        'Maternal Grandfather',
        'Maternal Grandmother',
        'Paternal Grandfather',
        'Paternal Grandmother',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable'],
            'type' => ['required', Rule::in(['familyHistory'])],
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', Rule::in($this->relationshipOptions)],
            'living_status' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'dob' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'mother' => ['nullable', 'string', 'max:255'],
            'father' => ['nullable', 'string', 'max:255'],
            'medical_history' => ['nullable', 'array'],
            'medical_history.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
