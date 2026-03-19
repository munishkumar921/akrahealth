<?php

namespace App\Http\Controllers;

use App\Helpers\AgoraTokenGenerator;
use App\Models\Appointment;

class AgoraController extends Controller
{
    /**
     * generateToken
     *
     * @param  mixed  $request
     * @return void
     */
    public function generateToken($doctor_id, $appointment_id = null)
    {
        $user = auth()->user();
        if ($user->doctor) {
            $uid = crc32($user->doctor->id);
        } elseif ($user->patient) {
            $uid = crc32($user->patient->id);
        } else {
            $uid = rand(10000, 99999);
        }

        $appointment = Appointment::where('id', $appointment_id)->first();

        $channelName = $this->generateChannelName($doctor_id, $appointment->patient_id);
        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $role = AgoraTokenGenerator::RolePublisher;
        $expireTimeInSeconds = 3600;
        $privilegeExpiredTs = now()->timestamp + $expireTimeInSeconds;

        $token = AgoraTokenGenerator::generateTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );

        if ($appointment_id) {
            Appointment::where('id', $appointment_id)->update([
                'agora_channel_id' => $channelName,
                'agora_channel_token' => $token,
            ]);
        }

        return [
            'token' => $token,
            'appId' => $appID,
            'channel' => $channelName,
            'uid' => $uid,
            'expire_at' => $privilegeExpiredTs,
        ];
    }

    /**
     * generateChannelName
     *
     * @param  mixed  $uuid1
     * @param  mixed  $uuid2
     */
    private function generateChannelName(string $uuid1, string $uuid2): string
    {
        $uuids = [$uuid1, $uuid2];
        sort($uuids);

        $channel_name = str_replace('-', '', substr($uuids[0], 0, 8).'_'.substr($uuids[1], 0, 8));

        return 'Channel_'.$channel_name;
    }
}
