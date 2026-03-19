<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestDemo;
use App\Services\DemoRequestService;
use Illuminate\Http\Request;

class DemoRequestController extends Controller
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
    public function store(RequestDemo $request, DemoRequestService $obj)
    {
        try {
            $obj->store($request);

            return redirect()
                ->route('signup')
                ->with('success', 'Your demo request has been sent successfully. Thank you!');

        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong! Please Try Again Later');

        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
