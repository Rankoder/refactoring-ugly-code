<?php

namespace App\Infrastructure;

use App\Domain\Repository\BinListProvider;
use GuzzleHttp\Exception\ClientException;

class BinListProviderImpl implements BinListProvider
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getCountryCode(string $bin): string
    {
        try {
            $response = $this->httpClient->get('https://lookup.binlist.net/' . $bin);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['country']['alpha2'] ?? '';
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 429) {
                echo "Rate limit exceeded. Please try again later." . PHP_EOL;
                exit(1);
            }
            throw $e;
        }
    }
}
