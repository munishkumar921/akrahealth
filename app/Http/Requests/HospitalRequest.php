<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

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
            'zip' => 'numeric',
            'country' => 'required|string',
            'phone' => 'required|numeric|min:10',
            'email' => 'required|email|max:255',
            'timezone' => 'nullable|string',
            'is_active' => 'nullable',
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
