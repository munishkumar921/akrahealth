<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Skill;
use App\Models\User;
use App\Traits\FileTrait;

class AssistantService
{
    use FileTrait;

    /**
     * update
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($request)
    {
        $input = $request->all();

        // $profile_photo_path =null;
        if ($request->file('profile_photo') != null) {
            $profile_photo_path = $this->uploadFile($request);

        } else {
            $profile_photo_path = $input['profile_photo'];
        }
        $skill = Skill::where('skill', $input['skills'])->pluck('id');
        $data = json_encode($input['language']);
        $language = json_decode($data);

        $user = User::updateOrCreate(
            ['slug' => $input['slug'] ?? null],
            [
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'name' => $input['first_name'].' '.$input['last_name'] ?? null,
                'sex' => $input['sex'] ?? null,
                'mobile' => $input['mobile'] ?? null,
                'description' => $input['description'] ?? null,
                'profile_photo_path' => $profile_photo_path,
                'skill_id' => $skill ?? null,
                'language' => $language ?? null,
            ]
        );

        if ($input['user_id'] == $user->id) {
            Address::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'address_1' => $input['address'] ?? null,
                    'is_default' => true,
                ]
            );
        }

    }
}
