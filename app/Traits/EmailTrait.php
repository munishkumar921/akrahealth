<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

trait EmailTrait
{
    /**
     * Send a Mailable immediately.
     *
     * @param  object  $mailable  Instance of Illuminate\Mail\Mailable
     * @param  string|array|null  $to  Recipient email or array of emails. If null, mailable must define recipients.
     */
    protected function sendMailable(object $mailable, $to = null): bool
    {
        try {
            if ($to) {
                Mail::to($to)->send($mailable);
            } else {
                Mail::send($mailable);
            }

            return true;
        } catch (Throwable $e) {
            Log::error('EmailTrait::sendMailable failed: '.$e->getMessage(), ['exception' => $e]);

            return false;
        }
    }

    /**
     * Queue a Mailable for background sending.
     *
     * @param  string|array|null  $to
     */
    protected function queueMailable(object $mailable, $to = null): bool
    {
        try {
            if ($to) {
                Mail::to($to)->queue($mailable);
            } else {
                Mail::queue($mailable);
            }

            return true;
        } catch (Throwable $e) {
            Log::error('EmailTrait::queueMailable failed: '.$e->getMessage(), ['exception' => $e]);

            return false;
        }
    }

    /**
     * Send a simple view-based email.
     *
     * @param  string|array  $to
     */
    protected function sendViewEmail(string $view, array $data, $to, string $subject = ''): bool
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to);
                if ($subject) {
                    $message->subject($subject);
                }
            });

            return true;
        } catch (Throwable $e) {
            Log::error('EmailTrait::sendViewEmail failed: '.$e->getMessage(), ['view' => $view, 'to' => $to, 'exception' => $e]);

            return false;
        }
    }

    /**
     * Helper to send UserCredentialsMail (if present in app/Mail).
     * Returns true on success, false on failure.
     *
     * @param  \App\Models\User  $user
     * @param  array  $payload  e.g. ['password' => 'plain']
     */
    protected function sendUserCredentialsMail($user, array $payload = []): bool
    {
        try {
            if (! class_exists(\App\Mail\UserCredentialsMail::class)) {
                Log::warning('UserCredentialsMail mailable not found.');

                return false;
            }
            $mailable = new \App\Mail\UserCredentialsMail($payload);
            Mail::to($user->email)->queue($mailable);

            return true;
        } catch (Throwable $e) {
            Log::error('EmailTrait::queueUserCredentialsMail failed: '.$e->getMessage(), ['user_id' => $user->id ?? null, 'exception' => $e]);

            return false;
        }
    }

    /**
     * Helper to send UserVerificationMail (if present in app/Mail).
     *
     * @param  \App\Models\User  $user
     * @param  array  $payload  e.g. ['token' => '...']
     */
    protected function sendUserVerificationMail($user, array $payload = []): bool
    {
        try {
            if (! class_exists(\App\Mail\UserVerificationMail::class)) {
                Log::warning('UserVerificationMail mailable not found.');

                return false;
            }
            $mailable = new \App\Mail\UserVerificationMail($payload);
            Mail::to($user->email)->send($mailable);

            return true;
        } catch (Throwable $e) {
            Log::error('EmailTrait::sendUserVerificationMail failed: '.$e->getMessage(), ['user_id' => $user->id ?? null, 'exception' => $e]);

            return false;
        }
    }
}
