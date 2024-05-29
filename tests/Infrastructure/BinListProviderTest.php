<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure;

use App\Infrastructure\BinListProvider;
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
 * Class BinListProviderTest
 *
 * @package App\Tests\Infrastructure
 */
class BinListProviderTest extends TestCase
{
    private $httpClient;
    private BinListProvider $binListProvider;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->binListProvider = new BinListProvider($this->httpClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetCountryCodeWithValidResponse(): void
    {
        $bin = '45717360';
        $responseBody = json_encode(['country' => ['alpha2' => 'US']]);
        $response = new Response(200, [], $responseBody);

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->andReturn($response);

        $countryCode = $this->binListProvider->getCountryCode($bin);

        $this->assertEquals('US', $countryCode);
    }

    public function testGetCountryCodeWithInvalidJsonResponse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $bin = '45717360';
        $responseBody = 'invalid_json';
        $response = new Response(200, [], $responseBody);

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->andReturn($response);

        $this->binListProvider->getCountryCode($bin);
    }

    public function testGetCountryCodeWithRateLimitExceeded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rate limit exceeded. Please try again later.');

        $bin = '45717360';
        $response = new Response(429);

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->andThrow(new ClientException('Rate limit exceeded', new Request('GET', 'https://lookup.binlist.net/' . $bin), $response));

        $this->binListProvider->getCountryCode($bin);
    }

    #[DataProvider('clientExceptionProvider')]
    public function testGetCountryCodeWithClientException(ClientException $exception, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $bin = '45717360';

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->andThrow($exception);

        $this->binListProvider->getCountryCode($bin);
    }

    public static function clientExceptionProvider(): array
    {
        $request = new Request('GET', 'https://lookup.binlist.net/45717360');
        return [
            [
                new ClientException('Client error', $request, new Response(400)),
                'Failed to fetch country code: Client error'
            ],
            [
                new ClientException('Rate limit exceeded', $request, new Response(429)),
                'Rate limit exceeded. Please try again later.'
            ]
        ];
    }

    public function testGetCountryCodeWithNonClientException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to fetch country code: Request error');

        $bin = '45717360';
        $request = new Request('GET', 'https://lookup.binlist.net/' . $bin);

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://lookup.binlist.net/' . $bin)
            ->andThrow(new RequestException('Request error', $request, new Response(500)));

        $this->binListProvider->getCountryCode($bin);
    }
}
