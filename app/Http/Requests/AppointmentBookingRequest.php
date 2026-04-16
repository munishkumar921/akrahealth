<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'accept_term_condition' => $this->boolean('accept_term_condition'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'exists:appointments,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'appointment_type' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'fee_amount' => ['nullable', 'numeric', 'min:0'],
            'createdAt' => ['required', 'date'],
            'accept_term_condition' => ['nullable', 'boolean'],
        ];
    }
}
