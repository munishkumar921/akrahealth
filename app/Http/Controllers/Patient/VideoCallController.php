<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Inertia\Inertia;
use Inertia\Response;

class VideoCallController extends Controller
{
    /**
     * videoCall
     */
    public function videoCall($id): Response
    {
        $appointment = Appointment::with('patient.user', 'doctor.user')->where('id', $id)->first();

        return Inertia::render('VideoCall', compact('appointment'));
    }
}
