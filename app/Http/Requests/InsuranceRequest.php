<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InsuranceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'facility' => 'required|string|max:255',
            'phone' => ['nullable', 'regex:/^[0-9+\-\s()]{10,20}$/'],
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => ['nullable', 'regex:/^[0-9][0-9\\-\\s]{2,19}$/'],
            'comments' => 'nullable|string|max:1000',
        ];
    }
}
