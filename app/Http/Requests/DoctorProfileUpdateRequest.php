<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoctorProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => $this->filled('mobile') ? preg_replace('/\s+/', '', (string) $this->input('mobile')) : $this->input('mobile'),
            'sex' => $this->filled('sex') ? trim((string) $this->input('sex')) : $this->input('sex'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'exists:doctors,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'first_name' => ['required', 'string', 'min:2', 'max:255'],
            'last_name' => ['required', 'string', 'min:1', 'max:255'],
            'mobile' => ['required', 'regex:/^[0-9+\-\s()]{10,20}$/'],
            'sex' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'experience' => ['nullable', 'string', 'max:100'],
            'about' => ['nullable', 'string'],
            'street_address1' => ['required', 'string', 'max:255'],
            'street_address2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['required', 'regex:/^[0-9][0-9\\-\\s]{2,19}$/'],
            'country' => ['required', 'string', 'max:100'],
            'specialities' => ['nullable', 'array'],
            'specialities.*' => ['nullable'],
            'profile_photo_path' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'certification' => ['nullable'],
        ];
    }
}
