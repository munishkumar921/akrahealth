<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SAdminGeneralSettingController extends Controller
{
    public function globalsetting()
    {
        return Inertia::render('SAdmin/generalsetting/GlobalSetting');
    }

    public function SMTPsetting()
    {
        return Inertia::render('SAdmin/generalsetting/SMTPSetting');
    }

    public function languagesetting()
    {
        return Inertia::render('SAdmin/generalsetting/LanguageSetting');
    }
}
