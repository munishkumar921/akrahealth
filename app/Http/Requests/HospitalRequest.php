<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HospitalRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'street_address1' => 'required|string|max:255',
            'street_address2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'zip' => ['nullable', 'regex:/^[0-9][0-9\\-\\s]{2,19}$/'],
            'country' => 'required|string',
            'phone' => ['required', 'regex:/^[0-9+\-\s()]{10,20}$/'],
            'email' => 'required|email|max:255',
            'timezone' => ['nullable'],
            'is_active' => 'required|boolean',
            'main_branch_id' => 'nullable|exists:hospitals,id',
            'weight_unit' => 'nullable|string|in:kg,lb',
            'height_unit' => 'nullable|string|in:cm,in,ft',
            'hc_unit' => 'nullable|string|in:cm,in',
        ];

        if ($this->hasFile('practice_logo')) {
            $rules['practice_logo'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->filled('phone') ? preg_replace('/\s+/', '', (string) $this->input('phone')) : $this->input('phone'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The branch name is required.',
            'street_address1.required' => 'Street address is required.',
            'city.required' => 'City is required.',
            'country.required' => 'Country is required.',
            'phone.required' => 'Phone number is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
