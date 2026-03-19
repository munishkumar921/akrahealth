<?php

namespace App\Http\Controllers\Biller;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class BillerController extends Controller
{
    public function dashboard()
    {
        // Basic dashboard data for Biller
        $dashboardData = [
            'messages' => 0,
            'encounters' => ['total' => 0],
            'patients' => ['total' => 0],
            'documents' => ['total' => 0],
            'calendars' => ['total' => 0],
            'reminders' => 0,
            'bills_to_process' => 0,
            'test_results_to_review' => 0,
        ];

        return Inertia::render('Biller/Dashboard', [
            'dashboardData' => $dashboardData,
        ]);
    }
}
