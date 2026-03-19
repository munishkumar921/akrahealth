<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserCredentialsMail;
use App\Mail\UserVerificationMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Models\UserVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    /**
     * Verify user email.
     *
     * @param  string  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify($token)
    {
        $verifyUser = UserVerify::where('token', $token)->first();

        if (! $verifyUser) {
            return redirect('/login')->with('error', 'Invalid verification token.');
        }

        $user = $verifyUser->user;

        if ($user->is_email_verified) {
            return redirect('/login')->with('success', 'Your email is already verified.');
        }

        $user->is_email_verified = true;
        $user->email_verified_at = now();
        $user->save();

        // Send welcome email
        // Mail::to($user->email)->send(new WelcomeMail(['name' => $user->name]));

        // Send user credentials
        // if ($user) {
        //      $mailData = [
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'password' => $user->password, // Send plain password
        //     ];
        //     try {
        //         $this->sendMailable(new UserCredentialsMail($mailData), $user->email);
        //      } catch (\Throwable $e) {
        //         \Log::error('Failed to send user credentials email: ' . $e->getMessage());
        //     }
        // }

        return redirect('/login')->with('success', 'Your email has been verified. You can now login.');
    }

    /**
     * Resend the verification email.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('error', 'No user found with that email address.');
        }

        if ($user->is_email_verified) {
            return back()->with('success', 'This email is already verified.');
        }

        $token = Str::random(64);
        UserVerify::updateOrCreate(
            ['user_id' => $user->id],
            ['token' => $token]
        );

        $data = ['name' => $user->name, 'token' => $token];

        Mail::to($user->email)->send(new UserVerificationMail($data));

        return back()->with('success', 'A new verification link has been sent to your email address.');
    }
}
