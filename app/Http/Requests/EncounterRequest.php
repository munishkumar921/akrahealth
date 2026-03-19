<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EncounterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|uuid|exists:patients,id',
            // 'appointment_id' => 'required|uuid|exists:appointments,id',
            'doctor_id' => 'required|uuid|exists:doctors,id',
            'encounter_type' => 'required|string',
            'encounter_location' => 'required|integer',
            'encounter_date_of_service' => 'required|date|before_or_equal:today',
            'chief_complaint' => 'required|string|max:1000',
            'encounter_role' => 'required|string|max:255',
        ];
    }
}
