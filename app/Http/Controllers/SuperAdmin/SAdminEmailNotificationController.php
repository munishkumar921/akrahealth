<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SAdminEmailNotificationController extends Controller
{
    public function massnotification()
    {
        return Inertia::render('SAdmin/emailnotification/MassNotification');
    }

    public function systemnotification()
    {
        return Inertia::render('SAdmin/emailnotification/SystemNotification');
    }

    public function massmailnotification()
    {
        return Inertia::render('SAdmin/emailnotification/MassMailNotification');
    }
}
