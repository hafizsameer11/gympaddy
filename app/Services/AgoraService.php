<?php

namespace App\Services;

use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class AgoraService
{
    protected string $appId = '2fae578d9eef4fe19df335eb67227571';
    protected string $appCertificate = '118e704beaea42e38b74b21a08bded63';

    public function generateRtcToken(string $channelName, int $uid, int $expireSeconds = 1800): string
    {
        $expireTime = time() + $expireSeconds;

        return RtcTokenBuilder::buildTokenWithUid(
            $this->appId,
            $this->appCertificate,
            $channelName,
            $uid,
            RtcTokenBuilder::RolePublisher,
            $expireTime
        );
    }

    public function generateRtcTokenWithRole(string $channelName, int $uid, string $role = 'host', int $expireSeconds = 1800): string
    {
        $expireTime = time() + $expireSeconds;

        $roleId = $role === 'host'
            ? RtcTokenBuilder::RolePublisher   // for broadcasters
            : RtcTokenBuilder::RoleSubscriber; // for audience

        return RtcTokenBuilder::buildTokenWithUid(
            $this->appId,
            $this->appCertificate,
            $channelName,
            $uid,
            $roleId,
            $expireTime
        );
    }
}
