<?php

namespace App\Services;

use GetStream\StreamChat\Client;

class StreamService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('STREAM_API_KEY'), env('STREAM_API_SECRET'));
    }

    public function createToken($userId)
    {
        return $this->client->createToken($userId);
    }
}
