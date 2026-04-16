<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * Update user's language preference
     */
    public function updateLanguage(Request $request)
    {
        $supportedLanguages = $this->supportedLanguages();

        $request->validate([
            'language' => ['required', 'string', 'in:'.implode(',', $supportedLanguages)],
        ]);

        $user = Auth::user();

        // Set locale immediately for this request
        app()->setLocale($request->language);

        // Store in session
        session(['user_language' => $request->language]);

        if ($user) {
            // Save language preference to user model if it has a language column
            if (in_array('language', $user->getFillable())) {
                $user->update(['language' => $request->language]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Language preference updated successfully',
            'language' => $request->language,
        ]);
    }

    /**
     * Get user's current language preference
     */
    public function getLanguage(Request $request)
    {
        $language = session('user_language', $this->defaultLanguage());

        if (Auth::check() && Auth::user()->language ?? false) {
            $language = Auth::user()->language;
        }

        if (! in_array($language, $this->supportedLanguages(), true)) {
            $language = $this->defaultLanguage();
        }

        return response()->json([
            'language' => $language,
        ]);
    }

    private function supportedLanguages(): array
    {
        $value = Setting::query()
            ->where('group', 'language')
            ->where('key', 'supported_languages')
            ->where('is_active', true)
            ->value('value');

        $languages = json_decode($value ?? '["en","ar","de","es"]', true);

        return is_array($languages) && ! empty($languages)
            ? array_values(array_unique(array_map('strval', $languages)))
            : ['en', 'ar', 'de', 'es'];
    }

    private function defaultLanguage(): string
    {
        return strtolower(
            Setting::query()
                ->where('group', 'language')
                ->where('key', 'default_language')
                ->where('is_active', true)
                ->value('value') ?? 'en'
        );
    }
}
