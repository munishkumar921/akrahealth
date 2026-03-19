<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * Update user's language preference
     */
    public function updateLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|string|in:en,es,fr,ar',
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
        $language = session('user_language', 'en');

        if (Auth::check() && Auth::user()->language ?? false) {
            $language = Auth::user()->language;
        }

        return response()->json([
            'language' => $language,
        ]);
    }
}
