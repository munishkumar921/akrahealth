<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => $this->filled('mobile') ? preg_replace('/\s+/', '', (string) $this->input('mobile')) : $this->input('mobile'),
            'hospital_phone' => $this->filled('hospital_phone') ? preg_replace('/\s+/', '', (string) $this->input('hospital_phone')) : $this->input('hospital_phone'),
            'is_active' => $this->boolean('is_active'),
            'is_verified' => $this->boolean('is_verified'),
        ]);
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'mobile' => ['required', 'regex:/^[0-9+\-\s()]{10,20}$/'],
            'password' => [$id ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'is_active' => ['required', 'boolean'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'string', 'max:100'],
            'specialities' => ['nullable', 'array'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'dea' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'appointment_slot_duration' => ['nullable', 'integer', 'min:0'],
            'hospital_name' => ['nullable', 'string', 'max:255'],
            'hospital_address' => ['nullable', 'string', 'max:255'],
            'hospital_phone' => ['nullable', 'regex:/^[0-9+\-\s()]{10,20}$/'],
            'is_verified' => ['required', 'boolean'],
            'profile_photo_path' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
            'certification' => ['nullable'],
            'government_id_proof' => ['nullable'],
            'old_certification' => ['nullable'],
            'old_government_id_proof' => ['nullable'],
        ];
    }
}
