<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ChartService;
use Illuminate\Http\Request;

class AChartsController extends Controller
{
    protected $chartService;

    public function __construct(ChartService $chartService)
    {
        $this->chartService = $chartService;
    }

    public function charts(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:hospitals,id',
        ]);

        $branchId = $request->input('branch_id');

        $zipPath = $this->chartService->generateAndZipChartsForBranch($branchId);

        if (! $zipPath) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate charts for the selected branch. There may be no patient data.',
            ], 404);
        }

        $downloadName = basename($zipPath);

        return response()->download($zipPath, $downloadName, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$downloadName.'"',
        ])->deleteFileAfterSend(true);
    }
}
