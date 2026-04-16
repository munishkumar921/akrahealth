<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HospitalTimingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'weekends' => $this->boolean('weekends'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'exists:hospital_timings,id'],
            'weekends' => ['nullable', 'boolean'],
            'time_zone' => ['required', Rule::in(timezone_identifiers_list())],
            'default_open_time' => ['nullable', 'date_format:H:i'],
            'default_close_time' => ['nullable', 'date_format:H:i'],
            'day_of_week' => ['required', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
        ];
    }
}
