<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionPlanRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,yearly',
            'currency' => 'required',
            'status' => 'required|in:0,1',
            'features' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->status,
        ]);
    }
}
