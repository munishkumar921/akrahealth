<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Common/Reports/Index');
    }
}
