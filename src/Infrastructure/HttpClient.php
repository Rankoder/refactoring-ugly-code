<?php

namespace App\Infrastructure;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function get(string $url): ResponseInterface
    {
        return $this->client->get($url);
    }
}
