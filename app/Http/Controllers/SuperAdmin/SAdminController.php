<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SAdminController extends Controller
{
    protected $userService;

    /**
     * __construct
     *
     * @param  mixed  $userService
     * @return void
     */
    public function __construct(UserService $userService)
    {
        // hasAccess('manage admins');
        $this->userService = $userService;
        $this->userService->role = 'SuperAdmin';
    }

    public function dashboard()
    {
        return Inertia::render('SAdmin/Dashboard');
    }

    /**
     * Roles & Permissions Management Page
     *
     * @return \Inertia\Response
     */
    public function rolespermission(Request $request)
    {
        $keyword = $request->get('keyword') ?? '';

        $roles = Roles::query();
        $roles = $roles->orderBy('created_at', 'desc')->paginate(request('per_page', 10))->withQueryString();

        return Inertia::render('SAdmin/RolesPermission', [
            'roles' => $roles,
            'keyword' => $keyword,
        ]);
    }

    /**
     * API: List all roles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesList(Request $request)
    {
        try {
            $keyword = $request->get('keyword', '');

            $query = Roles::query();

            if ($keyword) {
                $query->where('name', 'LIKE', '%'.$keyword.'%');
            }

            $roles = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Store a new role
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,'.$request->id,
                'guard_name' => 'nullable|string|max:255',
            ]);

            $role = \Spatie\Permission\Models\Role::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'guard_name' => $request->guard_name ?? 'web',
                    'is_active' => $request->has('is_status') ? (bool) $request->is_status : true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data' => $role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Delete a role
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesDestroy($id)
    {
        try {
            $role = \Spatie\Permission\Models\Role::findOrFail($id);
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Toggle role active status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesToggle(Request $request, int $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        try {
            $role = Roles::findOrFail($id);
            $role->is_active = $request->is_active;
            $role->save();

            return response()->json([
                'success' => true,
                'message' => 'Role status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role status: '.$e->getMessage(),
            ], 500);
        }
    }
}
