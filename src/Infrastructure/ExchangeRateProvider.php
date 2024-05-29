<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Repository\ExchangeRateProviderInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Class ExchangeRateProvider
 *
 * Provides exchange rates using an external HTTP API.
 */
class ExchangeRateProvider implements ExchangeRateProviderInterface
{
    private HttpClient $httpClient;

    /**
     * ExchangeRateProvider constructor.
     *
     * @param HttpClient $httpClient The HTTP client used to fetch exchange rates.
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get the exchange rate for the specified currency.
     *
     * @param string $currency The currency code (e.g., 'USD').
     * @return string The exchange rate as a string.
     * @throws InvalidArgumentException If the response from the API is invalid.
     * @throws GuzzleException
     */
    public function getRate(string $currency): string
    {
        try {
            $response = $this->httpClient->get('https://api.exchangeratesapi.io/latest');
            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON response');
            }

            if (!isset($data['rates'][$currency])) {
                return '1.0';
            }

            $rate = $data['rates'][$currency];

            if (str_contains((string)$rate, '.') && strlen(explode('.', (string)$rate)[1]) > 4) {
                throw new InvalidArgumentException('Rate precision exceeds the allowed limit');
            }

            return (string)$rate;
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Failed to fetch exchange rate: " . $e->getMessage());
        }
    }
}
