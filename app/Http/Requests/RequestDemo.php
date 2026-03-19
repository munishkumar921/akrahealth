<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestDemo extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'phone' => ['nullable', 'numeric'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
