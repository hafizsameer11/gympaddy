<?php

namespace App\Services;

use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class AgoraService
{
    public function generateRtcToken(string $channelName, int $uid, int $expireSeconds = 1800): string
{
    $appId = '2fae578d9eef4fe19df335eb67227571';
    $appCertificate = '118e704beaea42e38b74b21a08bded63';
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
