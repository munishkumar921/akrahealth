<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabRequest;
use App\Models\Lab;
use App\Services\UserService;
use Inertia\Inertia;

class DLabController extends Controller
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
        // hasAccess('manage doctors');
        $this->userService = $userService;
        $this->userService->role = 'Lab';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $laboratories = Lab::where('doctor_id', auth()->user()->doctor->id)->latest()->get();

        return Inertia::render('Doctors/Laboratories/Index', [
            'laboratories' => $laboratories,
        ]);
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
    public function store(LabRequest $request)
    {
        $this->userService->upsert($request->all());

        return redirect()->back()->with('success', 'Laboratory created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LabRequest $request, string $id)
    {
        $lab = Lab::where('doctor_id', auth()->user()->doctor->id)->findOrFail($id);
        $data = $request->all();

        $lab->update($data);

        return redirect()->back()->with('success', 'Laboratory updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lab = Lab::where('doctor_id', auth()->user()->doctor->id)->findOrFail($id);
        $lab->delete();

        return redirect()->back()->with('success', 'Laboratory deleted successfully.');
    }
}
