<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $user_id = $this->request->get('user_id');
        $rules = [
            'profile_photo_path' => ['nullable', 'mimes:png,jpg,jpeg', 'max:1000'],
            'first_name' => ['required', 'string', 'max:255', 'min:2'],
            'last_name' => ['required', 'string', 'max:255', 'min:2'],
            'street_address1' => ['required', 'string', 'max:500', 'min:2'],
            'street_address2' => ['nullable', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['required', 'regex:/^[0-9][0-9\\-\\s]{2,19}$/'],
            'country' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^[0-9+\-\s()]{10,20}$/'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user_id)],
            'sex' => ['nullable', 'string', 'max:6', 'in:Male,Female,Other'],
            'is_active' => ['required', 'boolean'],
            'dob' => ['nullable', 'date'],
            'question_id' => ['nullable', 'exists:questions,id', 'required_with:secret_answer'],
            'secret_answer' => ['nullable', 'string', 'max:255', 'min:2', 'required_with:question_id'],
            'password' => (! $user_id && ! (auth()->check() && auth()->user()->hasRole('Doctor')))
                ? ['required', 'confirmed', 'min:8']
                : ['nullable', 'confirmed', 'min:8'],

        ];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $routeId = $this->route('id') ?? $this->route('patient');

        if (! $this->filled('user_id') && $routeId) {
            $patient = Patient::find($routeId);
            if ($patient?->user_id) {
                $this->merge([
                    'user_id' => $patient->user_id,
                ]);
            }
        }

        $this->merge([
            'mobile' => $this->filled('mobile') ? preg_replace('/\s+/', '', (string) $this->input('mobile')) : $this->input('mobile'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'street_address1.required' => 'Street address is required.',
            'street_address1.min' => 'Street address must be at least 2 characters.',
            'city.required' => 'City is required.',
            'zip.required' => 'Zip code is required.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.numeric' => 'Mobile number must be numeric.',
            'mobile.digits_between' => 'Mobile number must be between 10 and 15 digits.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'sex.in' => 'Sex must be Male, Female, or Other.',
            'is_active.required' => 'Status is required.',
            'is_active.boolean' => 'Status must be active or inactive.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'profile_photo_path.mimes' => 'Profile photo must be a PNG or JPG image.',
            'profile_photo_path.max' => 'Profile photo must not exceed 1MB.',
            'secret_answer.required' => 'Secret answer is required.',
            'question_id.required' => 'Secret question is required.',
            'secret_answer.required' => 'Secret answer is required.',
            'secret_answer.min' => 'Secret answer must be at least 2 characters.',

        ];
    }
}
