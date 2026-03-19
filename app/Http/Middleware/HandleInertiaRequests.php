<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on first visit.
     */
    protected $rootView = 'app';

    /**
     * Asset versioning (prevents stale JS after deploy).
     * This ensures browser fetches fresh assets when code changes.
     */
    public function version(Request $request)
    {
        return parent::version($request).'-'.csrf_token();
    }

    /**
     * Shared Inertia props.
     */
    public function share(Request $request): array
    {
        /*
        * LOCALE & TRANSLATIONS
        */
        $locale = $request->session()->get('user_language', 'en');
        $user = $request->user();
        if ($user && $user->language) {
            $locale = $user->language;
        }
        $supportedLocales = ['en', 'es', 'fr', 'ar'];
        if (! in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }
        app()->setLocale($locale);

        /* Admin / Doctor switch */
        $selectedPatient = null;
        $isDoctorMode = false;
        $switchedDoctor = null;
        $switchedRole = null;
        if ($user) {
            $switchedRole = $request->session()->get('switched_role');

            /* Get user's original role */
            $originalRole = $user->getRoleNames()->first();

            /* Determine current role */
            $currentRole = $switchedRole ?: $originalRole;

            /* Check if user is currently in Doctor mode (either as actual Doctor or switched to Doctor) */
            $isDoctorMode = $currentRole === 'Doctor';
        }

        if ($isDoctorMode && $user->doctor) {
            $patientId = $user->doctor->selected_patient_id;
            if ($patientId) {
                $selectedPatient = Patient::with('user')->find($patientId);
            }
            $switchedDoctor = [
                'id' => $user->doctor->id,
                'name' => $user->doctor->name,
                'first_name' => $user->doctor->first_name,
                'last_name' => $user->doctor->last_name,
                'sex' => $user->sex,
                'profile_photo_url' => $user->doctor->profile_photo_url,
            ];
        }

        /* SHARED DATA */
        return array_merge(parent::share($request), [
            'locale' => app()->getLocale(),
            'translations' => [
                'common' => __('common'),
            ],
            'auth' => [
                'user' => $user ? $user->load('patient') : null,
                'patient' => $user ? $user->patient : null,
                'hasPatient' => $user ? $user->hasRole('Patient') : false,
                'doctor' => $user ? $user->doctor : null,
                'hasDoctor' => $user ? $user->hasRole('Doctor') : false,
                'admin' => $user ? $user->admin : null,
                'hasAdmin' => $user ? $user->hasRole('Admin') : false,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
            ],
            'selected_patient' => $selectedPatient,
            'razorpayKey' => config('services.razorpay.key'),
            'switched_role' => $switchedRole,
            'switched_doctor' => $switchedDoctor,
        ]);
    }
}
