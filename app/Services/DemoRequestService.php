<?php

namespace App\Services;

use App\Mail\DemoRequestMail;
use App\Models\DemoRequest;
use Illuminate\Support\Facades\Mail;

class DemoRequestService
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

        $user = new DemoRequest;
        $user->name = ucfirst($input['name']);
        $user->email = $input['email'] ?? '';
        $user->phone = $input['phone'] ?? '';
        $user->project = $input['project'] ?? '';
        $user->save();

        $data = ([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'project' => $input['project'] ?? '',
        ]);
        $email = config('mail.from.address');
        if ($user) {
            try {
                Mail::to($email)->send(new DemoRequestMail($data));
            } catch (\Exception $e) {
                //
            }
        } else {
            throw new \Exception('Error in processing request. Please try again later.');
        }

    }
}
