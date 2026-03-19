<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplementRequest extends FormRequest
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
            'purchase_date' => 'required|date',
            'sup_description' => 'required|string|max:255',
            'sup_strength' => 'nullable|string|max:100',
            'sup_manufacturer' => 'nullable|string|max:255',
            'sup_expiration' => 'nullable|date',
            'cpt' => 'nullable|string|max:20',
            'charge' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'sup_lot' => 'nullable|string|max:100',
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
            'purchase_date.required' => 'The date purchase is required.',
            'purchase_date.date' => 'Please enter a valid date.',
            'sup_description.required' => 'The description is required.',
            'sup_description.string' => 'The description must be a string.',
            'sup_description.max' => 'The description may not be greater than 255 characters.',
            'sup_strength.string' => 'The strength must be a string.',
            'sup_strength.max' => 'The strength may not be greater than 100 characters.',
            'sup_manufacturer.string' => 'The manufacturer must be a string.',
            'sup_manufacturer.max' => 'The manufacturer may not be greater than 255 characters.',
            'sup_expiration.date' => 'Please enter a valid expiration date.',
            'cpt.string' => 'The CPT must be a string.',
            'cpt.max' => 'The CPT may not be greater than 20 characters.',
            'charge.numeric' => 'The charge must be a number.',
            'charge.min' => 'The charge must be at least 0.',
            'quantity.required' => 'The quantity is required.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min' => 'The quantity must be at least 0.',
            'sup_lot.string' => 'The lot must be a string.',
            'sup_lot.max' => 'The lot may not be greater than 100 characters.',
        ];
    }
}
