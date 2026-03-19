<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HostpitalServices extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [

            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'street_address1' => ['nullable', 'string', 'max:150'],
            'street_address2' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'string', 'max:150'],
            'primary_contact' => ['nullable', 'string', 'max:100'],
            'npi' => ['nullable', 'string', 'max:100'],
            'medicare' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'default_pos_id' => ['nullable', 'string', 'max:20'],
            'documents_dir' => ['required', 'string', 'max:150'],
            'weight_unit' => ['required', 'string', 'in:lbs,kg'],
            'height_unit' => ['required', 'string', 'in:in,cm'],
            'temp_unit' => ['required', 'string', 'in:F,C'],
            'hc_unit' => ['required', 'string', 'in:in,cm'],
            'encounter_template' => ['required', 'string', 'max:100'],
            'additional_message' => ['nullable', 'string'],
            'reminder_interval' => ['nullable', 'string', 'max:50'],
            'billing_street_address1' => ['required', 'string', 'max:150'],
            'billing_street_address2' => ['nullable', 'string', 'max:150'],
            'billing_city' => ['required', 'string', 'max:100'],
            'billing_state' => ['required', 'string', 'max:100'],
            'billing_zip' => ['required', 'string', 'max:20'],
            'phaxio_api_key' => ['nullable', 'string', 'max:150'],
            'phaxio_api_secret' => ['nullable', 'string'],
            'birthday_extension' => ['nullable', 'string', 'in:y,n'],
            'birthday_message' => ['nullable', 'string'],
            'appointment_extension' => ['nullable', 'string', 'in:y,n'],
            'appointment_interval' => ['nullable', 'string', 'max:50'],
            'appointment_message' => ['nullable', 'string'],
            'sms_url' => ['nullable', 'string', 'max:150'],
            'practice_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],

        ];
    }
}
