<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AAppointmentController extends Controller
{
    public function visitTypeCreate()
    {
        return Inertia::render('Admin/Appointment/VisitTypeCreate');
    }

    public function exceptionCreate()
    {
        return Inertia::render('Admin/Appointment/ExceptionCreate');
    }
}
