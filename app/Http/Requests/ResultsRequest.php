<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResultsRequest extends FormRequest
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
        // Check if this is a reply request (has testsPerformed field)
        if ($this->has('testsPerformed') || $this->has('message')) {
            return [
                'testsPerformed' => ['nullable'],
                'message' => ['nullable', 'string'],
                'followup' => ['nullable', 'string'],
                'actionAfterSaving' => ['nullable', 'string'],
            ];
        }

        // Original rules for store/update results
        return [
            'patient_id' => ['nullable'],
            'doctor_id' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:Laboratory,Imaging'],
            'testName' => ['required', 'string', 'max:255'],
            'result' => ['required', 'string'],
            'result_units' => ['nullable', 'string', 'max:255'],
            'normal_reference_range' => ['required', 'string', 'max:255'],
            'flag' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'loinc_code' => ['nullable', 'string', 'max:255'],
            'doctor_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
