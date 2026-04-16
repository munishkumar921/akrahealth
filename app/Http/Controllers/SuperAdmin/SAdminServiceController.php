<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ServiceClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SAdminServiceController extends Controller
{
    protected ServiceClass $service;

    /**
     * __construct
     *
     * @param  mixed  $userService
     * @return void
     */
    public function __construct(ServiceClass $service)
    {
        // hasAccess('manage admins');
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $services = $this->service->list($request);
        $filters = [
            'keyword' => $request->input('keyword', ''),
            'category' => $request->input('category', ''),
            'status' => $request->input('status', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
        ];

        $metricsQuery = Service::query()
            ->when($filters['keyword'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('name', 'like', "%{$filters['keyword']}%")
                        ->orWhere('description', 'like', "%{$filters['keyword']}%")
                        ->orWhere('category', 'like', "%{$filters['keyword']}%");
                });
            })
            ->when($filters['category'] !== '', fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['status'] !== '', fn ($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->when($filters['date_from'] !== '', fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']));

        $metrics = [
            'total_services' => (clone $metricsQuery)->count(),
            'active_services' => (clone $metricsQuery)->where('is_active', true)->count(),
            'inactive_services' => (clone $metricsQuery)->where('is_active', false)->count(),
            'new_this_month' => (clone $metricsQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        return Inertia::render('SAdmin/Services/Index', [
            'services' => $services,
            'filters' => $filters,
            'metrics' => $metrics,
            'categories' => $this->service->getCategory(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:consultation,lab_test,pharmacy,home_healthcare,emergency,others'],
            'banner' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'old_banner' => ['nullable', 'string'],
            'remove_banner' => ['nullable', 'boolean'],
        ]);

        $this->service->upsert($validated);

        return redirect()->back()->with('success', 'Service saved successfully.');
    }

    public function show(Service $service)
    {
        $service->banner_url = $service->banner
            ? (str_starts_with($service->banner, 'http') ? $service->banner : \Storage::url($service->banner))
            : asset('images/avatar.webp');

        return response()->json($service);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:consultation,lab_test,pharmacy,home_healthcare,emergency,others'],
            'banner' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'old_banner' => ['nullable', 'string'],
            'remove_banner' => ['nullable', 'boolean'],
        ]);

        $validated['id'] = $service->id;

        $this->service->upsert($validated);

        return redirect()->back()->with('success', 'Service updated successfully.');
    }

    public function create()
    {
        return redirect()->route('superAdmin.services.index');
    }

    public function edit(Service $service)
    {
        return redirect()->route('superAdmin.services.index', ['edit' => $service->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        try {
            $service->delete();

            return redirect()->back()->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting service: '.$e->getMessage());
        }
    }

    public function toggleStatus(Service $service)
    {
        $service->update([
            'is_active' => ! $service->is_active,
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $service->is_active,
            'message' => $service->is_active ? 'Service activated successfully.' : 'Service deactivated successfully.',
        ]);
    }
}
