<?php

namespace App\Helpers;

use App\Helpers\AccessToken; // ✅ Make sure this is included

class RtcTokenBuilder
{
    const RolePublisher = 1;
    const RoleSubscriber = 2;

    public static function buildTokenWithUid($appId, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs)
    {
        return self::buildTokenWithUidAndPrivilege($appId, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
    }

    private static function buildTokenWithUidAndPrivilege($appId, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs)
    {
        $token = new AccessToken($appId, $appCertificate, $channelName, $uid);

        $token->addPrivilege(AccessToken::$Privileges["kJoinChannel"], $privilegeExpiredTs);
        
        if ($role == self::RolePublisher) {
            $token->addPrivilege(AccessToken::$Privileges["kPublishAudioStream"], $privilegeExpiredTs);
            $token->addPrivilege(AccessToken::$Privileges["kPublishVideoStream"], $privilegeExpiredTs);
            $token->addPrivilege(AccessToken::$Privileges["kPublishDataStream"], $privilegeExpiredTs);
        }

        return $token->build();
    }
}
