<?php

namespace App\Infrastructure;

use App\Domain\Repository\ExchangeRateProvider;

class ExchangeRateProviderImpl implements ExchangeRateProvider
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getRate(string $currency): float
    {
        $response = $this->httpClient->get('https://api.exchangeratesapi.io/latest');
        $data = json_decode($response->getBody()->getContents(), true);

        var_dump('###' . $data['rates'][$currency] . '###');
        return $data['rates'][$currency] ?? 1.0;
    }
}
