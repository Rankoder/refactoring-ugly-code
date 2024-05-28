<?php

namespace App\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use InvalidArgumentException;

/**
 * Class HttpClient
 *
 * A simple HTTP client wrapper for making GET requests using Guzzle.
 *
 * @package App\Infrastructure
 */
class HttpClient
{
    private Client $client;

    /**
     * HttpClient constructor.
     *
     * Initializes the Guzzle HTTP client.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Sends a GET request to the specified URL.
     *
     * @param string $url The URL to send the GET request to.
     *
     * @return ResponseInterface The response from the GET request.
     *
     * @throws InvalidArgumentException If the request fails.
     * @throws GuzzleException
     */
    public function get(string $url): ResponseInterface
    {
        try {
            return $this->client->get($url);
        } catch (RequestException $e) {
            throw new InvalidArgumentException('Failed to fetch data from URL: ' . $url, 0, $e);
        }
    }
}
