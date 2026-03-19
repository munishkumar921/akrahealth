<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AManageController extends Controller
{
    public function specialityCreate()
    {
        return \Inertia\Inertia::render('Admin/Manage/SpecialityCreate');
    }

    public function MedicineCreate()
    {
        return \Inertia\Inertia::render('Admin/Manage/MedicineCreate');
    }

    public function LabTestCategoryCreate()
    {
        return \Inertia\Inertia::render('Admin/Manage/LabTestCategoryCreate');
    }

    public function LabTestCreate()
    {
        return \Inertia\Inertia::render('Admin/Manage/LabTestCreate');
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
