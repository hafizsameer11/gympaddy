<?php

namespace App\Helpers;

class AccessToken
{
    public static $Privileges = [
        "kJoinChannel" => 1,
        "kPublishAudioStream" => 2,
        "kPublishVideoStream" => 3,
        "kPublishDataStream" => 4
    ];

    private $appId;
    private $appCertificate;
    private $channelName;
    private $uid;
    private $salt;
    private $ts;
    private $messages;

    public function __construct($appId, $appCertificate, $channelName, $uid)
    {
        $this->appId = $appId;
        $this->appCertificate = $appCertificate;
        $this->channelName = $channelName;
        $this->uid = $uid;
        $this->ts = time() + 24 * 3600;
        $this->salt = rand(1, 99999999);
        $this->messages = [];
    }

    public function addPrivilege($privilege, $expireTs)
    {
        $this->messages[$privilege] = $expireTs;
    }

    public function build()
    {
        $data = json_encode([
            "salt" => $this->salt,
            "ts" => $this->ts,
            "privileges" => $this->messages
        ]);

        $signature = hash_hmac('sha256', $this->appId . $this->channelName . $this->uid . $data, $this->appCertificate, true);
        $content = pack("v", strlen($this->appId)) . $this->appId .
                   pack("v", strlen($signature)) . $signature .
                   pack("v", strlen($data)) . $data;

        return base64_encode($content);
    }
}
