<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'facility' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',

            // Phone number validation
            'phone' => 'required|regex:/^[0-9\-\+\s()]{7,20}$/',

            // PIN Code
            'pin_code' => 'required|digits_between:4,10',

            // Optional fields
            'email' => 'nullable|email|max:255',
            'comment' => 'nullable|string|max:500',

            // Optional numeric field
            'ordering_id' => 'nullable',

            // Boolean field (0/1, true/false)
        ];
    }
}
