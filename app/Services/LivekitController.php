<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LivekitService;
use Illuminate\Http\Request;

class LivekitController extends Controller
{
    public function startRecording(Request $request)
    {
        $request->validate([
            'roomName' => 'required|string',
        ]);

        try {
            $egressId = LivekitService::startRecording($request->roomName);

            return response()->json(['egressId' => $egressId]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to start recording: '.$e->getMessage()], 500);
        }
    }

    public function stopRecording(Request $request)
    {
        $request->validate([
            'egressId' => 'required|string',
        ]);

        try {
            LivekitService::stopRecording($request->egressId);

            return response()->json(['message' => 'Recording stopped successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to stop recording: '.$e->getMessage()], 500);
        }
    }
}
