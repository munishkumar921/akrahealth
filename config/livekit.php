<?php

return [
    /*
    | LiveKit API Credentials
    */
    'api_key' => env('LIVEKIT_API_KEY'),
    'api_secret' => env('LIVEKIT_API_SECRET'),
    'host' => env('LIVEKIT_HOST', 'http://localhost:7880'),

    /*
    | Recording output
    | recordings_path: where LiveKit egress writes the file (on the LiveKit host)
    | public_base_url: prefix to build a public download URL if LiveKit does not return one
    | Example:
    |   LIVEKIT_RECORDINGS_PATH=recordings/{room_name}-{time}.mp4
    |   LIVEKIT_RECORDINGS_BASE_URL=https://cdn.example.com
    */
    'recordings_path' => env('LIVEKIT_RECORDINGS_PATH', 'recordings/{room_name}-{time}.mp4'),
    'public_base_url' => env('LIVEKIT_RECORDINGS_BASE_URL', null),
];
