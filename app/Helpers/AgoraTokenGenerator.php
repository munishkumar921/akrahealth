<?php

namespace App\Helpers;

use App\Services\Agora\RtcTokenBuilder2;

class AgoraTokenGenerator
{
    const RolePublisher = 1;

    /**
     * generateTokenWithUid
     *
     * @param  mixed  $appId
     * @param  mixed  $appCertificate
     * @param  mixed  $channelName
     * @param  mixed  $uid
     * @param  mixed  $role
     * @param  mixed  $privilegeExpireTs
     * @return void
     */
    public static function generateTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs)
    {
        $uid = $request->uid ?? 0;
        $role = RtcTokenBuilder2::ROLE_PUBLISHER;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = now()->timestamp;
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder2::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );
    }
}
