<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\SystemAlertMail;
use App\Models\Setting;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SAdminGeneralSettingController extends Controller
{
    public function globalsetting()
    {
        return Inertia::render('SAdmin/generalsetting/GlobalSetting', [
            'settings' => $this->getGlobalSettings(),
        ]);
    }

    public function updateGlobalSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_website' => ['nullable', 'url', 'max:255'],
            'site_email' => ['required', 'email', 'max:255'],
            'time_zone' => ['required', 'string', 'max:255'],
            'default_user_group' => ['nullable', 'string', 'max:255'],
            'support_email' => ['required', 'boolean'],
            'user_notification' => ['required', 'boolean'],
            'user_support' => ['required', 'boolean'],
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
            'enable_live_chat' => ['required', 'boolean'],
            'live_chat_link' => ['nullable', 'url', 'max:500'],
            'enable_recaptcha' => ['required', 'boolean'],
            'recaptcha_site_key' => ['nullable', 'string', 'max:255'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:255'],
            'enable_analytics' => ['required', 'boolean'],
            'google_analytics_id' => ['nullable', 'string', 'max:255'],
            'enable_maps' => ['required', 'boolean'],
            'google_maps_key' => ['nullable', 'string', 'max:255'],
            'enable_gdpr' => ['required', 'boolean'],
        ]);

        $this->persistSettings(
            'global',
            [
                'site_name' => $validated['site_name'],
                'site_website' => $validated['site_website'] ?? null,
                'site_email' => $validated['site_email'],
                'time_zone' => $validated['time_zone'],
                'default_user_group' => $validated['default_user_group'] ?? null,
                'support_email' => $this->toOnOff($validated['support_email']),
                'user_notification' => $this->toOnOff($validated['user_notification']),
                'user_support' => $this->toOnOff($validated['user_support']),
                'default_theme' => $validated['theme'],
                'live_chat' => $this->toOnOff($validated['enable_live_chat']),
                'live_chat_link' => $validated['live_chat_link'] ?? null,
                'recaptcha_enable' => $this->toOnOff($validated['enable_recaptcha']),
                'recaptcha_site_key' => $validated['recaptcha_site_key'] ?? null,
                'recaptcha_secret_key' => $validated['recaptcha_secret_key'] ?? null,
                'analytics_enable' => $this->toOnOff($validated['enable_analytics']),
                'analytics_id' => $validated['google_analytics_id'] ?? null,
                'maps_enable' => $this->toOnOff($validated['enable_maps']),
                'maps_key' => $validated['google_maps_key'] ?? null,
                'gdpr' => (string) (int) $validated['enable_gdpr'],
            ],
            [
                'recaptcha_secret_key',
            ]
        );

        return redirect()->route('superAdmin.globalsetting')->with('success', 'Global settings updated successfully.');
    }

    public function SMTPsetting()
    {
        return Inertia::render('SAdmin/generalsetting/SMTPSetting', [
            'smtp' => $this->getSmtpSettings(),
        ]);
    }

    public function updateSMTPSettings(Request $request)
    {
        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
        ]);

        $this->persistSettings(
            'smtp',
            [
                'smtp_host' => $validated['host'],
                'smtp_port' => (string) $validated['port'],
                'smtp_username' => $validated['username'] ?? null,
                'smtp_password' => $validated['password'] ?? null,
                'smtp_from_address' => $validated['from_address'],
                'smtp_from_name' => $validated['from_name'],
                'smtp_encryption' => $validated['encryption'],
            ],
            [
                'smtp_password',
            ]
        );

        return redirect()->route('superAdmin.smtpsetting')->with('success', 'SMTP settings updated successfully.');
    }

    public function testSMTPSettings(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $smtp = $this->getSmtpSettings();

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $smtp['host']);
        Config::set('mail.mailers.smtp.port', (int) $smtp['port']);
        Config::set('mail.mailers.smtp.username', $smtp['username']);
        Config::set('mail.mailers.smtp.password', $smtp['password']);
        Config::set('mail.mailers.smtp.encryption', $smtp['encryption'] === 'none' ? null : $smtp['encryption']);
        Config::set('mail.from.address', $smtp['from_address']);
        Config::set('mail.from.name', $smtp['from_name']);

        try {
            Mail::mailer('smtp')->to($validated['email'])->send(new SystemAlertMail([
                'subject' => $validated['subject'],
                'message' => $validated['message'],
            ]));

            return redirect()->route('superAdmin.smtpsetting')->with('success', 'Test email sent successfully.');
        } catch (\Throwable $exception) {
            app(InAppNotificationService::class)->notifySuperAdmins(
                app(InAppNotificationService::class)->buildPayload(
                    'SMTP failure',
                    'SMTP test email failed to send from Super Admin settings.',
                    'smtp_failure',
                    [
                        'meta' => [
                            'to' => $validated['email'],
                            'error' => $exception->getMessage(),
                        ],
                    ]
                )
            );

            return redirect()->route('superAdmin.smtpsetting')->withErrors([
                'smtp' => 'SMTP test failed: '.$exception->getMessage(),
            ]);
        }
    }

    public function languagesetting()
    {
        return Inertia::render('SAdmin/generalsetting/LanguageSetting', [
            'languageSettings' => $this->getLanguageSettings(),
        ]);
    }

    public function updateLanguageSettings(Request $request)
    {
        $validated = $request->validate([
            'languages' => ['required', 'array', 'min:1'],
            'languages.*.name' => ['required', 'string', 'max:255'],
            'languages.*.code' => ['required', 'string', 'max:20', 'distinct'],
            'languages.*.enabled' => ['required', 'boolean'],
            'default_language' => ['required', 'string', 'max:20'],
        ]);

        $languages = collect($validated['languages'])
            ->map(function (array $language) {
                return [
                    'name' => trim($language['name']),
                    'code' => strtolower(trim($language['code'])),
                    'enabled' => (bool) $language['enabled'],
                ];
            })
            ->unique('code')
            ->values();

        $defaultLanguage = strtolower(trim($validated['default_language']));
        $defaultIndex = $languages->search(fn (array $language) => $language['code'] === $defaultLanguage);

        if ($defaultIndex === false) {
            return back()->withErrors([
                'default_language' => 'The default language must exist in the language list.',
            ]);
        }

        $languages[$defaultIndex]['enabled'] = true;

        $this->persistSettings('language', [
            'supported_languages' => json_encode(
                $languages->where('enabled', true)->pluck('code')->values()->all(),
                JSON_UNESCAPED_UNICODE
            ),
            'default_language' => $defaultLanguage,
            'language_catalog' => json_encode($languages->values()->all(), JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->route('superAdmin.languagesetting')->with('success', 'Language settings updated successfully.');
    }

    private function getGlobalSettings(): array
    {
        $settings = $this->settingsByGroup('global');

        return [
            'site_name' => $settings['site_name'] ?? config('app.name'),
            'site_website' => $settings['site_website'] ?? config('app.url'),
            'site_email' => $settings['site_email'] ?? env('MAIL_FROM_ADDRESS', 'support@akrahealth.com'),
            'time_zone' => $settings['time_zone'] ?? config('app.timezone', 'Asia/Kolkata'),
            'default_user_group' => $settings['default_user_group'] ?? 'marketing',
            'support_email' => $this->toBool($settings['support_email'] ?? true),
            'user_notification' => $this->toBool($settings['user_notification'] ?? true),
            'user_support' => $this->toBool($settings['user_support'] ?? true),
            'theme' => $settings['default_theme'] ?? 'light',
            'enable_live_chat' => $this->toBool($settings['live_chat'] ?? false),
            'live_chat_link' => $settings['live_chat_link'] ?? '',
            'enable_recaptcha' => $this->toBool($settings['recaptcha_enable'] ?? false),
            'recaptcha_site_key' => $settings['recaptcha_site_key'] ?? '',
            'recaptcha_secret_key' => $settings['recaptcha_secret_key'] ?? '',
            'enable_analytics' => $this->toBool($settings['analytics_enable'] ?? false),
            'google_analytics_id' => $settings['analytics_id'] ?? '',
            'enable_maps' => $this->toBool($settings['maps_enable'] ?? false),
            'google_maps_key' => $settings['maps_key'] ?? '',
            'enable_gdpr' => $this->toBool($settings['gdpr'] ?? false),
        ];
    }

    private function getSmtpSettings(): array
    {
        $settings = $this->settingsByGroup('smtp');

        return [
            'host' => $settings['smtp_host'] ?? config('mail.mailers.smtp.host'),
            'port' => $settings['smtp_port'] ?? config('mail.mailers.smtp.port'),
            'username' => $settings['smtp_username'] ?? config('mail.mailers.smtp.username'),
            'password' => $settings['smtp_password'] ?? config('mail.mailers.smtp.password'),
            'from_address' => $settings['smtp_from_address'] ?? config('mail.from.address'),
            'from_name' => $settings['smtp_from_name'] ?? config('mail.from.name'),
            'encryption' => $settings['smtp_encryption'] ?? (config('mail.mailers.smtp.encryption') ?: 'none'),
        ];
    }

    private function getLanguageSettings(): array
    {
        $settings = $this->settingsByGroup('language');
        $catalog = $this->decodeJsonSetting($settings['language_catalog'] ?? null);
        $supportedLanguages = $this->decodeJsonSetting($settings['supported_languages'] ?? null);
        $defaultLanguage = strtolower($settings['default_language'] ?? 'en');

        $languages = collect(is_array($catalog) ? $catalog : $this->defaultLanguageCatalog())
            ->map(function (array $language) use ($supportedLanguages) {
                $code = strtolower($language['code'] ?? '');

                return [
                    'name' => $language['name'] ?? strtoupper($code),
                    'code' => $code,
                    'enabled' => is_array($supportedLanguages)
                        ? in_array($code, $supportedLanguages, true)
                        : (bool) ($language['enabled'] ?? false),
                ];
            })
            ->filter(fn (array $language) => $language['code'] !== '')
            ->unique('code')
            ->values();

        if (! $languages->contains(fn (array $language) => $language['code'] === $defaultLanguage)) {
            $fallback = $this->defaultLanguageCatalog()
                ->firstWhere('code', $defaultLanguage) ?? ['name' => strtoupper($defaultLanguage), 'code' => $defaultLanguage];

            $languages->push([
                'name' => $fallback['name'],
                'code' => $fallback['code'],
                'enabled' => true,
            ]);
        }

        $languages = $languages->map(function (array $language) use ($defaultLanguage) {
            if ($language['code'] === $defaultLanguage) {
                $language['enabled'] = true;
            }

            return $language;
        })->values();

        return [
            'languages' => $languages->all(),
            'default_language' => $defaultLanguage,
        ];
    }

    private function settingsByGroup(string $group): array
    {
        return Setting::query()
            ->where('group', $group)
            ->where('is_active', true)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    private function decodeJsonSetting(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return json_decode($value, true);
    }

    private function defaultLanguageCatalog(): Collection
    {
        return collect([
            ['name' => 'English', 'code' => 'en', 'enabled' => true],
            ['name' => 'العربية', 'code' => 'ar', 'enabled' => true],
            ['name' => 'Deutsch', 'code' => 'de', 'enabled' => true],
            ['name' => 'Español', 'code' => 'es', 'enabled' => true],
        ]);
    }

    private function persistSettings(string $group, array $values, array $encryptedKeys = []): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'type' => 'string',
                    'group' => $group,
                    'description' => ucfirst(str_replace('_', ' ', $key)),
                    'is_encrypted' => in_array($key, $encryptedKeys, true),
                    'value' => $value,
                    'is_active' => true,
                    'updated_by' => auth()->id(),
                    'created_by' => auth()->id(),
                ]
            );
        }
    }

    private function toBool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function toOnOff(bool $value): string
    {
        return $value ? 'on' : 'off';
    }
}
