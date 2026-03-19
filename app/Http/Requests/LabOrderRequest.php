<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabOrderRequest extends FormRequest
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
        $rules = [
            // Required fields for all lab orders
            'insurance_id' => ['required', 'exists:insurances,id'],
            'encounter_id' => ['required', 'exists:encounters,id'],
            'encounter_provider' => ['required'],

            // Lab-specific fields (required when creating lab orders)
            'labs' => ['required_if:type,labs', 'string', 'max:5000'],
            'labs_icd' => ['nullable', 'array'],
            'labs_icd.*' => ['string', 'max:20'],

            // Radiology-specific fields
            'radiology' => ['required_if:type,radiology', 'string', 'max:5000'],
            'radiology_icd' => ['nullable', 'array'],
            'radiology_icd.*' => ['string', 'max:20'],

            // Cardiopulmonary-specific fields
            'cp' => ['required_if:type,cardiopulmonary', 'string', 'max:5000'],
            'cp_icd' => ['nullable', 'array'],
            'cp_icd.*' => ['string', 'max:20'],

            // Referral-specific fields
            'referrals' => ['required_if:type,referrals', 'string', 'max:5000'],
            'referrals_icd' => ['nullable', 'array'],
            'referrals_icd.*' => ['string', 'max:20'],

            // Common optional fields
            'notes' => ['nullable', 'string', 'max:2000'],
            'pending_date' => ['nullable', 'date', 'after:today'],
            // 'action_after_saving' => ['nullable', 'string', 'in:print,print_queue'],

        ];

        // For updates, make some fields optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['insurance_id'] = ['sometimes', 'exists:insurances,id'];
            $rules['lab_id'] = ['sometimes', 'exists:labs,id'];
            $rules['encounter_id'] = ['sometimes', 'exists:encounters,id'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'insurance_id.required' => 'Please select an insurance provider.',
            'lab_id.required' => 'Please select a laboratory provider.',
            'encounter_id.required' => 'A valid encounter is required.',
            'labs.required_if' => 'Please specify the lab tests to be performed.',
            'radiology.required_if' => 'Please specify the radiology/imaging tests.',
            'cp.required_if' => 'Please specify the cardiopulmonary tests.',
            'referrals.required_if' => 'Please specify the referral details.',
            'labs.max' => 'Lab test description cannot exceed 5000 characters.',
            'radiology.max' => 'Radiology description cannot exceed 5000 characters.',
            'cp.max' => 'Cardiopulmonary description cannot exceed 5000 characters.',
            'referrals.max' => 'Referral description cannot exceed 5000 characters.',
            'notes.max' => 'Notes cannot exceed 2000 characters.',
            'pending_date.after' => 'Pending date must be a future date.',
            'action_after_saving.in' => 'Invalid action selected.',
            'labs_icd.*.max' => 'Each ICD code cannot exceed 20 characters.',
            'radiology_icd.*.max' => 'Each ICD code cannot exceed 20 characters.',
            'cp_icd.*.max' => 'Each ICD code cannot exceed 20 characters.',
            'referrals_icd.*.max' => 'Each ICD code cannot exceed 20 characters.',
            'encounter_provider.required' => 'Lab Provider is required',

        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'labs' => 'lab tests',
            'radiology' => 'radiology/imaging tests',
            'cp' => 'cardiopulmonary tests',
            'referrals' => 'referral details',
            'labs_icd' => 'lab diagnosis codes',
            'radiology_icd' => 'radiology diagnosis codes',
            'cp_icd' => 'cardiopulmonary diagnosis codes',
            'referrals_icd' => 'referral diagnosis codes',
            'pending_date' => 'order pending date',
            'action_after_saving' => 'action after saving',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if at least one order type is specified
            $orderTypes = ['labs', 'radiology', 'cp', 'referrals'];
            $hasOrderType = false;

            foreach ($orderTypes as $type) {
                if ($this->filled($type) && ! empty($this->input($type))) {
                    $hasOrderType = true;
                    break;
                }
            }

            if (! $hasOrderType) {
                $validator->errors()->add('order_type', 'At least one type of order (lab, radiology, cardiopulmonary, or referral) must be specified.');
            }

            // Validate that the selected lab belongs to the doctor's hospital
            if ($this->filled('lab_id')) {
                $lab = \App\Models\Lab::find($this->input('lab_id'));
                $doctorHospitalId = auth()->user()->doctor->hospital_id ?? null;

                if ($lab && $doctorHospitalId && $lab->hospital_id !== $doctorHospitalId) {
                    $validator->errors()->add('lab_id', 'The selected laboratory provider is not associated with your hospital.');
                }
            }

            // Validate encounter belongs to the patient and doctor
            if ($this->filled('encounter_id')) {
                $encounter = \App\Models\Encounter::find($this->input('encounter_id'));
                $doctorId = auth()->user()->doctor->id ?? null;
                $patientId = auth()->user()->doctor->selected_patient_id ?? null;

                if ($encounter) {
                    if ($encounter->doctor_id !== $doctorId) {
                        $validator->errors()->add('encounter_id', 'The selected encounter does not belong to you.');
                    }
                    if ($encounter->patient_id !== $patientId) {
                        $validator->errors()->add('encounter_id', 'The selected encounter does not belong to the current patient.');
                    }
                }
            }
        });
    }
}
