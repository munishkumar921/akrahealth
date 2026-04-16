<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        // adjust as needed (e.g. check permissions)
        return true;
    }

    public function rules()
    {
        $id = $this->request->get('id') ?: $this->route('id') ?: $this->route('admin') ?: $this->route('user');

        return [
            'id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
            'mobile' => [
                'nullable',
                'regex:/^[0-9+\-\s()]{10,20}$/',
            ],
            'password' => [$id ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'is_active' => ['required', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'role' => ['sometimes', 'required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'regex:/^[0-9][0-9\\-\\s]{2,19}$/'],
            'street_address1' => ['nullable', 'string', 'max:500'],
            'street_address2' => ['nullable', 'string', 'max:500'],
            'hospitalId' => ['nullable', 'exists:hospitals,id'],
            'speciality' => ['nullable'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'profile_photo_path' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => $this->filled('mobile') ? preg_replace('/\s+/', '', (string) $this->input('mobile')) : $this->input('mobile'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
