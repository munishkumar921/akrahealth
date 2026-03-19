<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PharmacyRequest extends FormRequest
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
            // Contact Person Information
            'first_name' => ['required', 'string', 'max:255', 'min:2'],
            'last_name' => ['nullable', 'string', 'max:255', 'min:2'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user_id ?? null),
            ],
            'mobile' => ['required', 'string', 'max:20', 'min:10'],

            // Pharmacy Details
            'pharmacy_name' => ['required', 'string', 'max:255', 'min:2'],
            'license_number' => ['required', 'string', 'max:100'],
            'pharmacy_mobile' => ['required', 'string', 'max:20', 'min:10'],
            'pharmacy_email' => ['required', 'email', 'max:255'],

            // Profile Photo
            'profile_photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

            // Address Information
            'street_address1' => ['required', 'string', 'max:500', 'min:5'],
            'street_address2' => ['nullable', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['required', 'string', 'max:20', 'min:3'],

            // Operational Hours
            'opening_time' => ['nullable', 'date_format:h:i:s A'],
            'closing_time' => ['nullable', 'date_format:h:i:s A', 'after:opening_time'],

            // Status Fields
            'is_active' => ['required', 'boolean'],
            'is_verified' => ['required', 'boolean'],

            // About (optional)
            'about' => ['nullable', 'string', 'max:2000'],

            // Banner (optional file upload)
            'banner' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],

            // Other documents/images (optional file uploads)
            'license' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:5120'],
            'gst_license' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:5120'],
            'store_front_photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'owner_id_proof' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:5120'],
        ];

        // For updates, make all fields optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($rules as $key => $rule) {
                $rules[$key] = ['sometimes', ...$rule];
            }
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
            // Contact Person Messages
            'first_name.required' => 'Please enter the contact person\'s first name.',
            'first_name.string' => 'First name must be a valid text.',
            'first_name.max' => 'First name cannot exceed 255 characters.',
            'first_name.min' => 'First name must be at least 2 characters.',

            'last_name.string' => 'Last name must be a valid text.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',
            'last_name.min' => 'Last name must be at least 2 characters.',

            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address cannot exceed 255 characters.',
            'email.unique' => 'This email address is already registered.',

            'mobile.required' => 'Please enter a mobile number.',
            'mobile.string' => 'Mobile number must be a valid text.',
            'mobile.max' => 'Mobile number cannot exceed 20 characters.',
            'mobile.min' => 'Mobile number must be at least 10 characters.',

            // Pharmacy Details Messages
            'pharmacy_name.required' => 'Please enter the pharmacy name.',
            'pharmacy_name.string' => 'Pharmacy name must be a valid text.',
            'pharmacy_name.max' => 'Pharmacy name cannot exceed 255 characters.',
            'pharmacy_name.min' => 'Pharmacy name must be at least 2 characters.',

            'license_number.required' => 'Please enter the license number.',
            'license_number.string' => 'License number must be a valid text.',
            'license_number.max' => 'License number cannot exceed 100 characters.',

            'pharmacy_mobile.required' => 'Please enter the pharmacy contact number.',
            'pharmacy_mobile.string' => 'Pharmacy mobile number must be a valid text.',
            'pharmacy_mobile.max' => 'Pharmacy mobile number cannot exceed 20 characters.',
            'pharmacy_mobile.min' => 'Pharmacy mobile number must be at least 10 characters.',

            'pharmacy_email.required' => 'Please enter the pharmacy email address.',
            'pharmacy_email.email' => 'Please enter a valid pharmacy email address.',
            'pharmacy_email.max' => 'Pharmacy email address cannot exceed 255 characters.',

            // Profile Photo Messages
            'profile_photo.file' => 'Profile photo must be a valid file.',
            'profile_photo.mimes' => 'Profile photo must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'profile_photo.max' => 'Profile photo cannot exceed 2MB in size.',

            // Address Messages
            'street_address1.required' => 'Please enter the address.',
            'street_address1.string' => 'Address must be a valid text.',
            'street_address1.max' => 'Address cannot exceed 500 characters.',
            'street_address1.min' => 'Address must be at least 5 characters.',

            'street_address2.string' => 'Secondary address must be a valid text.',
            'street_address2.max' => 'Secondary address cannot exceed 500 characters.',

            'city.required' => 'Please enter the city.',
            'city.string' => 'City must be a valid text.',
            'city.max' => 'City name cannot exceed 100 characters.',

            'country.required' => 'Please select a country.',
            'country.string' => 'Country must be a valid text.',
            'country.max' => 'Country name cannot exceed 100 characters.',

            'state.string' => 'State must be a valid text.',
            'state.max' => 'State name cannot exceed 100 characters.',

            'zip.required' => 'Please enter the zip code.',
            'zip.string' => 'Zip code must be a valid text.',
            'zip.max' => 'Zip code cannot exceed 20 characters.',
            'zip.min' => 'Zip code must be at least 3 characters.',

            // Operational Hours Messages
            'opening_time.date_format' => 'Opening time must be in HH:MM:SS AM/PM format (e.g., 09:00:00 AM).',

            'closing_time.date_format' => 'Closing time must be in HH:MM:SS AM/PM format (e.g., 09:00:00 PM).',
            'closing_time.after' => 'Closing time must be after opening time.',

            // Status Messages
            'is_active.required' => 'Please select the status.',
            'is_verified.required' => 'Please select the verification status.',

            // About Messages
            'about.string' => 'About description must be a valid text.',
            'about.max' => 'About description cannot exceed 2000 characters.',

            // Banner Messages
            'banner.file' => 'Banner must be a valid file.',
            'banner.mimes' => 'Banner must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'banner.max' => 'Banner cannot exceed 5MB in size.',
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
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'mobile' => 'mobile number',
            'street_address1' => 'address',
            'street_address2' => 'secondary address',
            'country' => 'country',
            'state' => 'state',
            'city' => 'city',
            'zip' => 'zip code',
            'is_verified' => 'verification status',
            'is_active' => 'status',
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
            // Validate email format more strictly
            if ($this->filled('email')) {
                $email = $this->input('email');
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('email', 'Please enter a valid email address format.');
                }
            }

            // Validate mobile number format only if provided (allows international formats)
            if ($this->filled('mobile')) {
                $mobile = $this->input('mobile');
                // Remove common formatting characters
                $cleanMobile = preg_replace('/[\s\-\(\)\.]+/', '', $mobile);
                if (! preg_match('/^[0-9]{10,20}$/', $cleanMobile)) {
                    $validator->errors()->add('mobile', 'Please enter a valid mobile number (10-20 digits).');
                }
            }
        });
    }
}
