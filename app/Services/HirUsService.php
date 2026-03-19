<?php

namespace App\Services;

use App\Mail\DoctorAssistantMail;
use App\Models\DoctorAssistant;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class HirUsService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        $Assistant = User::where('slug', $input['slug'])->first();
        $doctor = User::where('id', auth()->user()->id)->first();
        $data1 = DoctorAssistant::where('user_id', auth()->user()->id)->first();

        $data = Skill::whereIn('skill', $input['skills'])->pluck('id');

        if (! isset($data1)) {
            $token = Str::random(64);
            $user = new DoctorAssistant;
            $user->user_id = auth()->user()->id;
            $user->doctor_assistant_id = $Assistant->id;
            $user->skill_id = json_encode($data);
            $user->token = $token;
            $user->is_accepted = 0;
            $user->save();

            $data = ([
                'doctor_name' => $doctor->name,
                'skills' => $input['skills'],
                'message' => $input['message'],
                'assistant_name' => $Assistant->name,
                'token' => $token,
            ]);

            $email = env('MAIL_FROM_ADDRESS');

            if ($user->user_id == auth()->user()->id) {
                Mail::to($Assistant->email)->cc($email)->send(new DoctorAssistantMail($data));

                return back()->with('success', 'Your  request has been sent successfully.Thank you');
            } else {
                return back()->with('error', 'Something went wrong! Please Try Again Later');
            }
        } elseif ($data1->is_accepted == true) {
            return back()->with('error', 'You have already selected this assistant');

        } else {

            $data = ([
                'doctor_name' => $doctor->name,
                'skills' => $input['skills'],
                'message' => $input['message'],
                'assistant_name' => $Assistant->name,
                'token' => $data1->token,
            ]);
            $email = env('MAIL_FROM_ADDRESS');
            Mail::to($Assistant->email)->cc($email)->send(new DoctorAssistantMail($data));

            return back()->with('success', 'This invitation email has been resent');
        }

    }
}
