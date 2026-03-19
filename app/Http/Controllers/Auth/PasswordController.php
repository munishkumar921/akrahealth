<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Services\PasswordChangeService;
use Inertia\Inertia;

class PasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function changePassword()
    {
        return Inertia::render('Auth/ChangePassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatePassword(PasswordRequest $request, PasswordChangeService $obj)
    {
        $obj->store($request);

        return back()->with('success', 'Password changed successfully!');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
