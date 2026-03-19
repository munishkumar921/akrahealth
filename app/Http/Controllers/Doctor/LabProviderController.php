<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\LabTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LabProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labProviders = LabTest::where('doctor_id', Auth::user()->doctor->id)
            ->orderBy('name')
            ->get();

        return response()->json($labProviders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $labProvider = LabTest::create([
            'doctor_id' => Auth::user()->doctor->id,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'state' => $request->state,
            'country' => $request->country,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lab provider created successfully',
            'data' => $labProvider,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $labProvider = LabTest::where('doctor_id', Auth::user()->doctor->id)
            ->findOrFail($id);

        return response()->json($labProvider);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $labProvider = LabTest::where('doctor_id', Auth::user()->doctor->id)
            ->findOrFail($id);

        $labProvider->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lab provider updated successfully',
            'data' => $labProvider,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $labProvider = LabTest::where('doctor_id', Auth::user()->doctor->id)
            ->findOrFail($id);

        $labProvider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lab provider deleted successfully',
        ]);
    }

    public function dashboard()
    {
        return Inertia::render('Lab/Dashboard');
    }
}
