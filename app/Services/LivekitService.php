<?php

namespace App\Services;

use Firebase\JWT\JWT;
use LiveKit\Egress\EgressClient;
use LiveKit\Egress\RoomCompositeEgressRequest;
use LiveKit\Proto\FileOutput;

class LivekitService
{
    public static function generateToken(string $identity, string $room): string
    {
        $apiKey = config('services.livekit.api_key');
        $apiSecret = config('services.livekit.api_secret');

        if (empty($apiKey) || empty($apiSecret)) {
            throw new \Exception('LiveKit API key or secret is missing in .env');
        }

        $now = time();
        $exp = $now + 3600; // 1 hour validity

        $grant = [
            'roomJoin' => true,
            'room' => $room,
        ];

        $payload = [
            'iss' => $apiKey,
            'sub' => $identity,
            'nbf' => $now,
            'exp' => $exp,
            'video' => $grant,
        ];

        return JWT::encode($payload, $apiSecret, 'HS256');
    }

    public static function startRecording(string $roomName): string
    {
        $host = config('livekit.host');
        $apiKey = config('livekit.api_key');
        $apiSecret = config('livekit.api_secret');
        $recordingsPath = config('livekit.recordings_path', 'recordings/{room_name}-{time}.mp4');

        $egressClient = new EgressClient($host, $apiKey, $apiSecret);

        $request = new RoomCompositeEgressRequest;
        $request->setRoomName($roomName);

        // This example uses a file output, but you can also use stream outputs.
        // The filename can include placeholders like {room_name} and {time}.
        $fileOutput = new FileOutput;
        $fileOutput->setFilepath($recordingsPath);
        $request->setFileOutput($fileOutput);

        $egressInfo = $egressClient->startRoomCompositeEgress($request);

        return $egressInfo->getEgressId();
    }

    /**
     * Stop a recording and return any available file metadata so the
     * frontend can offer a download link.
     */
    public static function stopRecording(string $egressId): array
    {
        $host = config('livekit.host');
        $apiKey = config('livekit.api_key');
        $apiSecret = config('livekit.api_secret');
        $publicBase = rtrim((string) config('livekit.public_base_url'), '/');

        $egressClient = new EgressClient($host, $apiKey, $apiSecret);
        $egressInfo = $egressClient->stopEgress($egressId);

        // Attempt to surface the recorded file location if LiveKit returns it
        $fileResults = method_exists($egressInfo, 'getFileResults') ? $egressInfo->getFileResults() : [];
        $first = is_array($fileResults) && count($fileResults) ? $fileResults[0] : null;

        $location = $first && method_exists($first, 'getLocation') ? $first->getLocation() : null;
        $filepath = $first && method_exists($first, 'getFilepath') ? $first->getFilepath() : null;
        $filename = $first && method_exists($first, 'getFilename') ? $first->getFilename() : null;

        // Build a public URL if LiveKit did not return one but a base URL is configured
        $downloadUrl = $location;
        if (! $downloadUrl && $publicBase && $filepath) {
            $downloadUrl = $publicBase.'/'.ltrim($filepath, '/');
        }

        return [
            'location' => $downloadUrl,
            'filepath' => $filepath,
            'filename' => $filename,
        ];
    }
}
