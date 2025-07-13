<?php

namespace App\Services;

use GetStream\StreamChat\Client;

class StreamService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('STREAM_API_KEY', '298uez2pm5kq'), env('STREAM_API_SECRET', 'p7cvzwcj6yq3dgvez9etmqgx4rcp75wszngvvckzepcpu6fp2us7s5ajx5gx3t6g'));
    }

    public function createToken($userId)
    {
        return $this->client->createToken($userId);
    }
}
