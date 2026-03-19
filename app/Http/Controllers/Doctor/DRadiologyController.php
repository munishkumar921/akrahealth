<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Radiology;
use Illuminate\Http\Request;

class DRadiologyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'is_verified' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        $address_id = Address::create([
            'address_1' => $validated['address_1'] ?? null,
            'address_2' => $validated['address_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip' => $validated['zip'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ])->id;
        Radiology::create([
            'hospital_id' => auth()->user()->doctor->hospital_id,
            'doctor_id' => auth()->user()->doctor->id,
            'created_by' => auth()->user()->name,
            'name' => $validated['name'],
            'facility_type' => 'Radiology',
            'address_id' => $address_id,
            'website' => $validated['website'] ?? null,
            'is_verified' => $validated['is_verified'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Radiology provider added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Radiology $radiology)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Radiology $radiology)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Radiology $radiology)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Radiology $radiology)
    {
        //
    }
}
