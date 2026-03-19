<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PharmacyRequest;
use App\Models\Country;
use App\Models\Pharmacy;
use App\Models\State;
use App\Services\PharmacyService;
use App\Services\UserService;

class APharmacyController extends Controller
{
    protected $pharmacyService;

    protected $userService;

    /**
     * __construct
     *
     * @param  mixed  $pharmacyService
     * @return void
     */
    public function __construct(PharmacyService $pharmacyService, UserService $userService)
    {
        $this->pharmacyService = $pharmacyService;
        $this->userService = $userService;
        $this->userService->role = 'Pharmacy';

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pharmacies = $this->pharmacyService->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return inertia('Admin/Pharmacy/PharmacyList', [
            'pharmacies' => $pharmacies,
            'request' => $request,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pharmacy = new Pharmacy;

        return inertia('Admin/PharmacyCreate', ['pharmacy' => $pharmacy, 'states' => State::select('id', 'name')->get(),
            'countries' => Country::select('id', 'name')->get(), ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PharmacyRequest $request)
    {
        $user = $this->userService->upsert($request->all());

        return redirect()->route('admin.pharmacies.index')->with('success', 'Pharmacy created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Pharmacy::where('id', $id)->delete();

        return redirect()->route('admin.pharmacies.index')->with('success', 'Pharmacy deleted successfully.');
    }
}
