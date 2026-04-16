<?php

namespace App\Http\Middleware;

use App\Models\Setting;
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
        $languageSettings = $this->getLanguageSettings();
        $defaultLocale = $languageSettings['default_language'];
        $supportedLocales = $languageSettings['supported_languages'];

        $locale = $request->session()->get('user_language', $defaultLocale);
        $user = $request->user();
        if ($user && $user->language) {
            $locale = $user->language;
        }
        if (! in_array($locale, $supportedLocales)) {
            $locale = $defaultLocale;
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
            'supported_languages' => $supportedLocales,
            'default_language' => $defaultLocale,
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

    private function getLanguageSettings(): array
    {
        $settings = Setting::query()
            ->where('group', 'language')
            ->where('is_active', true)
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        $defaultLanguage = strtolower($settings['default_language'] ?? 'en');
        $supportedLanguages = json_decode($settings['supported_languages'] ?? '["en","ar","de","es"]', true);

        if (! is_array($supportedLanguages) || empty($supportedLanguages)) {
            $supportedLanguages = ['en', 'ar', 'de', 'es'];
        }

        if (! in_array($defaultLanguage, $supportedLanguages, true)) {
            array_unshift($supportedLanguages, $defaultLanguage);
            $supportedLanguages = array_values(array_unique($supportedLanguages));
        }

        return [
            'default_language' => $defaultLanguage,
            'supported_languages' => $supportedLanguages,
        ];
    }
}
