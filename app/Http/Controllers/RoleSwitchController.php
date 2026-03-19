<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleSwitchController extends Controller
{
    public function toggle(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return abort(401);
        }

        $hasAdmin = $user->roles()->whereIn('name', ['Admin', 'SuperAdmin'])->exists();
        if (! $hasAdmin) {
            return abort(403);
        }

        // Toggle flag - switching back to Admin
        if (session('switched_role') === 'Doctor') {
            session()->forget('switched_role');

            // Check if there's a stored last admin page to redirect back to
            $lastAdminPage = session('admin_last_page');
            session()->forget('admin_last_page');

            // Redirect to last admin page if available, otherwise go to dashboard
            $redirectUrl = $lastAdminPage ? $lastAdminPage : route('admin.dashboard');

            return redirect($redirectUrl)->with('success', 'Switched back to Admin view');
        }

        // Before switching to the Doctor view, store the last page the admin was on.
        // We use the 'referer' header, which gives us the previous URL.
        $referer = $request->header('referer');

        // We only want to store the URL if it's a valid page we can return to.
        // We check if the referer exists and is not the doctor dashboard to avoid incorrect redirects.
        if ($referer && ! str_contains($referer, 'doctor.dashboard')) {
            session(['admin_last_page' => $referer]);
        }

        session(['switched_role' => 'Doctor']);

        // redirect to doctor dashboard with success message
        return redirect()->route('doctor.dashboard')->with('success', 'Switched to Doctor view');
    }
}
