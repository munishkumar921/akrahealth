<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    /**
     * list
     *
     * @return void
     */
    public function list($request)
    {
        return Setting::query()
            ->whereNot('group', 'Razorpay')
            ->when($request->search, function ($query) use ($request) {
                $query->where('key', 'like', "%{$request->search}%")
                    ->orWhere('group', 'like', "%{$request->search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();
    }

    /**
     * update
     *
     * @param  mixed  $data
     * @param  mixed  $request
     * @return void
     */
    public function upsert($data)
    {
        $user = auth()->user();
        if (isset($data['id']) && $data['id'] != '') {
            $data['updated_by'] = $user ? $user->id : null;
        } else {
            $data['created_by'] = $user ? $user->id : null;
        }
        $data['is_encrypted'] = 1;

        Setting::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
            ],
            $data
        );
    }

    /**
     * set
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value)
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => 'Razorpay',
            ]
        );
    }

    /**
     * getSettingByGroup
     *
     * @param  mixed  $group
     * @return void
     */
    public function getSettingByGroup($group)
    {
        return Setting::where('group', $group)->get();
    }

    /**
     * getSettingByKey
     *
     * @param  mixed  $key
     * @return void
     */
    public function getSettingByKey($key)
    {
        return Setting::where('key', $key)->first();
    }
}
