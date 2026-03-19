<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Rules\isValidPasswordRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SignupRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $user_id = (int) $this->request->get('id');

        return [
            'profile_photo_path' => ['nullable', 'mimes:png,jpg', 'max:1000'],
            'first_name' => ['required', 'string', 'max:255', 'min:2'],
            'last_name' => ['nullable', 'string', 'max:255', 'min:2'],
            'sex' => ['nullable', 'string', 'max:6', 'in:Male,Female,Other'],
            'speciality_id' => ['nullable'],
            'street_address1' => ['nullable', 'string', 'max:500', 'min:2'],
            'street_address2' => ['nullable', 'string', 'max:500', 'min:2'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'mobile' => ['required', 'numeric', 'min:10'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user_id)],
            'password' => ['required', 'confirmed', 'string', new isValidPasswordRule],
            'password_confirmation' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'mimes:pdf,docx,doc', 'max:10000'],
            'practice_name' => ['required', 'string', 'max:255'],
            'practice_email' => ['required', 'email', 'max:255'],
            'practice_primary_contact' => ['nullable', 'numeric', 'min:10'],
            'practice_phone' => ['nullable', 'numeric', 'min:10', 'max:15'],
            'practice_street_address1' => ['nullable', 'string', 'max:500'],
            'practice_street_address2' => ['nullable', 'string', 'max:500'],
            'practice_city' => ['nullable', 'string', 'max:100'],
            'practice_state' => ['nullable', 'string', 'max:100'],
            'practice_zip' => ['nullable', 'string', 'max:20'],
            'practice_country' => ['nullable', 'string', 'max:100'],
            'practice_address' => ['nullable', 'string', 'max:500'],
            'secret_answer' => ['required', 'string', 'max:255'],
            'question_id' => ['required', 'string', 'max:255'],
            'subscription_plan_id' => ['nullable', 'uuid', 'exists:subscription_plans,id'],

        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sex' => 'Gender must be Male, Female, or Other.',
            'speciality_id.required' => 'Speciality is required.',
            'question_id.required' => 'Secret Question is required.',
        ];
    }
}
