<?php

namespace App\Infrastructure;

use App\Domain\Repository\BinListProviderInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Class BinListProvider
 *
 * Provides BIN list data using an external HTTP API.
 */
class BinListProvider implements BinListProviderInterface
{
    private HttpClient $httpClient;

    /**
     * BinListProvider constructor.
     *
     * @param HttpClient $httpClient The HTTP client used to fetch BIN list data.
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get the country code for the specified BIN.
     *
     * @param string $bin The BIN number.
     * @return string The country code (e.g., 'US').
     * @throws InvalidArgumentException If the response from the API is invalid.
     * @throws GuzzleException
     */
    public function getCountryCode(string $bin): string
    {
        try {
            $response = $this->httpClient->get('https://lookup.binlist.net/' . $bin);
            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON response');
            }

            return $data['country']['alpha2'] ?? '';
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 429) {
                throw new InvalidArgumentException('Rate limit exceeded. Please try again later.');
            }
            throw new InvalidArgumentException('Failed to fetch country code: ' . $e->getMessage());
        } catch (RequestException $e) {
            throw new InvalidArgumentException('Failed to fetch country code: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            throw new InvalidArgumentException('Failed to fetch country code: ' . $e->getMessage());
        }
    }
}
