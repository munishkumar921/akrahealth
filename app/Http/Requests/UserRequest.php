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
        // Try to detect an ID from common route parameter names for update requests
        $id = $this->request->get('id');

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],
            // roles might be an array or single value depending on your service

            // additional optional fields used by your service
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'speciality' => ['nullable'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'profile_photo_path' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
