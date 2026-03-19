<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Services\AddressServices;

class ConfigurationController extends Controller
{
    public function address_book()
    {
        return \Inertia\Inertia::render('Common/Configure/AddressBook/Index');
    }

    public function my_forms()
    {
        return \Inertia\Inertia::render('Common/Configure/Forms/Index');
    }

    public function provider_exceptions()
    {
        return \Inertia\Inertia::render('Common/Configure/ProviderExceptions/Index');
    }

    public function schedule_setup()
    {
        return \Inertia\Inertia::render('Common/Configure/ScheduleSetup/Index');
    }

    public function visit_type()
    {
        return \Inertia\Inertia::render('Common/Configure/VisitTypes/Index');
    }

    public function addressStore(AddressRequest $request, AddressServices $obj)
    {
        $obj->store($request->all());

        return redirect()->back()->with('success', 'Address saved successfully.');
    }
}
