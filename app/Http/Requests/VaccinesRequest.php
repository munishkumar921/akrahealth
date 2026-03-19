<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VaccinesRequest extends FormRequest
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
            'date_purchase' => 'required|date',
            'immunization' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'lot' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'expiration' => 'nullable|date',
            'cpt' => 'nullable|string|max:20',
            'code' => 'nullable|string|max:20',
            'quantity' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_purchase.required' => 'The date purchase is required.',
            'date_purchase.date' => 'Please enter a valid date.',
            'immunization.required' => 'The immunization field is required.',
            'immunization.string' => 'The immunization must be a string.',
            'immunization.max' => 'The immunization may not be greater than 255 characters.',
            'brand.string' => 'The brand must be a string.',
            'brand.max' => 'The brand may not be greater than 255 characters.',
            'lot.string' => 'The lot must be a string.',
            'lot.max' => 'The lot may not be greater than 100 characters.',
            'manufacturer.string' => 'The manufacturer must be a string.',
            'manufacturer.max' => 'The manufacturer may not be greater than 255 characters.',
            'expiration.date' => 'Please enter a valid expiration date.',
            'cpt.string' => 'The CPT must be a string.',
            'cpt.max' => 'The CPT may not be greater than 20 characters.',
            'code.string' => 'The code must be a string.',
            'code.max' => 'The code may not be greater than 20 characters.',
            'quantity.required' => 'The quantity is required.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min' => 'The quantity must be at least 0.',
        ];
    }
}
