<?php

namespace App\Services;

use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class AgoraService
{
    public function generateRtcToken(string $channelName, int $uid, int $expireSeconds = 1800): string
    {
        $appId = config('services.agora.app_id', env('AGORA_APP_ID'));
        $appCertificate = config('services.agora.certificate', env('AGORA_APP_CERTIFICATE'));
        $expireTime = time() + $expireSeconds;

        return RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            RtcTokenBuilder::RolePublisher,
            $expireTime
        );
    }
}
