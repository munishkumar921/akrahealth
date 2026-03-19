<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ServiceClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SAdminServiceController extends Controller
{
    protected $service;

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
    public function index()
    {
        $services = $this->service->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return Inertia::render('SuperAdmin/Service/Index', compact('services', 'request', 'keyword'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->service->upsert($request->all());

        return redirect()->back()->with('success', 'Service saved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Service::where('id', $id)->delete();

            return redirect()->back()->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting service: '.$e->getMessage());
        }
    }
}
