<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SAdminUserController extends Controller
{
    public function userdashboard()
    {
        return Inertia::render('SAdmin/user/Dashboard');
    }

    public function userlist(Request $request)
    {
        $query = User::role('Admin');

        // Apply search filter
        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('mobile', 'like', '%'.$search.'%');
            });
        }

        // Apply status filters
        if ($request->has('filterActive') && $request->filterActive && (! $request->has('filterInactive') || ! $request->filterInactive)) {
            $query->where('is_active', true);
        } elseif ($request->has('filterInactive') && $request->filterInactive && (! $request->has('filterActive') || ! $request->filterActive)) {
            $query->where('is_active', false);
        }
        // If both or neither, no filter

        $admins = $query->with('roles')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('SAdmin/user/UserList', [
            'admins' => $admins,
            'states' => State::get(),
            'countries' => Country::get(),
            'request' => $request->all(), // Pass request params back for form state
        ]);
    }

    public function useractivitymonitoring()
    {
        return Inertia::render('SAdmin/user/UserActivityMonitoring');
    }
}
