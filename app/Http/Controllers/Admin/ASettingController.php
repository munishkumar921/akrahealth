<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ASettingController extends Controller
{
    public function subscriptionPlanCreate()
    {
        return \Inertia\Inertia::render('Admin/Setting/SubscriptionPlanCreate');
    }

    public function scheduleSetupCreate()
    {
        return \Inertia\Inertia::render('Admin/Setting/ScheduleSetupCreate');
    }

    public function serviceCreate()
    {
        return \Inertia\Inertia::render('Admin/Setting/ServiceCreate');
    }

    public function generalCreate()
    {
        return \Inertia\Inertia::render('Admin/Setting/GeneralCreate');
    }

    public function appointmantCreate()
    {
        return \Inertia\Inertia::render('Admin/Setting/AppointmantCreate');
    }

    public function schedulSetupCreate()
    {
        return \Inertia\Inertia::render('Admin/Setting/SchedulSetupCreate');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
