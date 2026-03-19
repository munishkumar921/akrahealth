<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\LivekitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LivekitController extends Controller
{
    /**
     * conferenceCall
     *
     * @return void
     */
    public function conferenceCall($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $user = auth()->user();
        $user_id = $user ? $user->id : null;
        $role = auth()->user()->roles[0]->name ?? 'guest';
        /* Room name from request, fallback to default */
        $room = $appointment->patient_id.'_'.$appointment->doctor_id.'_room';

        /*
        * Ensure each participant identity is unique
        * You can safely include user ID or email
        */
        $identity = $user->id
            ? "{$user_id}_{$role}"
            : 'guest_'.uniqid();

        /* Generate LiveKit access token */
        $token = LivekitService::generateToken($identity, $room);

        return inertia('LiveKitVideoCall', [
            'token' => $token,
            'url' => config('services.livekit.ws_url'),
            'room' => $room,
            'identity' => $identity,
        ]);
    }

    public function generateToken($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $user = auth()->user();
        $user_id = $user ? $user->id : null;
        $role = auth()->user()->roles[0]->name ?? 'guest';
        /* Room name from request, fallback to default */
        $room = $appointment->patient_id.'_'.$appointment->doctor_id.'_room';

        /*
        * Ensure each participant identity is unique
        * You can safely include user ID or email
        */
        $identity = $user->id
            ? "{$user_id}_{$role}"
            : 'guest_'.uniqid();

        /* Generate LiveKit access token */
        $token = LivekitService::generateToken($identity, $room);
        $data = [
            'token' => $token,
            'url' => config('services.livekit.ws_url'),
            'room' => $room,
            'identity' => $identity,
        ];

        return $data;
    }

    /**
     * Start LiveKit composite recording for a room.
     */
    public function startRecording(Request $request): JsonResponse
    {
        $roomName = $request->input('roomName');
        if (! $roomName) {
            return response()->json(['error' => 'roomName is required'], 422);
        }

        try {
            $egressId = LivekitService::startRecording($roomName);

            return response()->json([
                'egressId' => $egressId,
                'message' => 'Recording started',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Stop LiveKit recording by egress id and return download metadata.
     */
    public function stopRecording(Request $request): JsonResponse
    {
        $egressId = $request->input('egressId');
        if (! $egressId) {
            return response()->json(['error' => 'egressId is required'], 422);
        }

        try {
            $result = LivekitService::stopRecording($egressId);

            return response()->json([
                'message' => 'Recording stopped',
                'download_url' => $result['location'] ?? null,
                'file_path' => $result['filepath'] ?? null,
                'file_name' => $result['filename'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
