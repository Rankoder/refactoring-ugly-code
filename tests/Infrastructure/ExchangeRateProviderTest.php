<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure;

use App\Infrastructure\ExchangeRateProvider;
use App\Infrastructure\HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Mockery;

/**
 * Class ExchangeRateProviderTest
 *
 * @package App\Tests\Infrastructure
 */
class ExchangeRateProviderTest extends TestCase
{
    private $httpClient;
    private ExchangeRateProvider $exchangeRateProvider;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->exchangeRateProvider = new ExchangeRateProvider($this->httpClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    #[DataProvider('validResponseProvider')]
    public function testGetRateWithValidResponse(array $responseData, string $currency, string $expectedRate): void
    {
        $responseBody = json_encode($responseData);
        $response = new Response(200, [], $responseBody);

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://api.exchangeratesapi.io/latest')
            ->andReturn($response);

        $rate = $this->exchangeRateProvider->getRate($currency);

        $this->assertEquals($expectedRate, $rate);
    }

    public static function validResponseProvider(): array
    {
        return [
            [
                ['rates' => ['USD' => '1.1234']],
                'USD',
                '1.1234'
            ],
            [
                ['rates' => ['EUR' => '1.0']],
                'EUR',
                '1.0'
            ],
            [
                ['rates' => ['GBP' => '0.8573']],
                'GBP',
                '0.8573'
            ],
            [
                ['rates' => []],
                'JPY',
                '1.0'
            ]
        ];
    }

    public function testGetRateWithInvalidJsonResponse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $responseBody = 'invalid_json';
        $response = new Response(200, [], $responseBody);

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://api.exchangeratesapi.io/latest')
            ->andReturn($response);

        $this->exchangeRateProvider->getRate('USD');
    }

    public function testGetRateWithPrecisionExceeds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rate precision exceeds the allowed limit');

        $responseBody = json_encode(['rates' => ['USD' => '1.12345']]);
        $response = new Response(200, [], $responseBody);

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://api.exchangeratesapi.io/latest')
            ->andReturn($response);

        $this->exchangeRateProvider->getRate('USD');
    }

    public function testGetRateWithRequestException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to fetch exchange rate: Request error');

        $request = new Request('GET', 'https://api.exchangeratesapi.io/latest');
        $this->httpClient
            ->shouldReceive('get')
            ->with('https://api.exchangeratesapi.io/latest')
            ->andThrow(new RequestException('Request error', $request));

        $this->exchangeRateProvider->getRate('USD');
    }

    public function testGetRateWithClientException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to fetch exchange rate: Client error');

        $request = new Request('GET', 'https://api.exchangeratesapi.io/latest');
        $response = new Response(400);

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://api.exchangeratesapi.io/latest')
            ->andThrow(new ClientException('Client error', $request, $response));

        $this->exchangeRateProvider->getRate('USD');
    }
}
