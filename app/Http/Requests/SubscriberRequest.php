<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming admin access is handled elsewhere
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:pending,active,suspend,reject',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'razorpay_subscription_id' => 'nullable|string|max:255',
            'payment_link_id' => 'nullable|string|max:255',
            'payment_status' => 'nullable|in:pending,paid,failed',
            'currency' => 'required|in:USD,INR,EUR,GBP',
            'amount' => 'required|numeric|min:0',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Any data preparation if needed
    }
}
