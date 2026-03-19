<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordChangeService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($request)
    {
        $input = $request->all();
        User::where('id', auth()->user()->id)->update([
            'password' => Hash::make($input['password']),
        ]);
    }
}
