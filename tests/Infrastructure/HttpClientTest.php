<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure;

use App\Infrastructure\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Mockery;

/**
 * Class HttpClientTest
 *
 * @package App\Tests\Infrastructure
 */
class HttpClientTest extends TestCase
{
    private $clientMock;
    private HttpClient $httpClient;

    protected function setUp(): void
    {
        $this->clientMock = Mockery::mock(Client::class);
        $this->httpClient = new HttpClient();

        // Reflection to access private property
        $reflection = new \ReflectionClass($this->httpClient);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->httpClient, $this->clientMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    #[DataProvider('validUrlProvider')]
    public function testGetWithValidUrl(string $url, array $responseBody): void
    {
        $response = new Response(200, [], json_encode($responseBody));

        $this->clientMock
            ->shouldReceive('get')
            ->with($url)
            ->andReturn($response);

        $result = $this->httpClient->get($url);

        $this->assertEquals($response, $result);
    }

    public static function validUrlProvider(): array
    {
        return [
            ['https://api.example.com/data', ['key' => 'value']],
            ['https://api.example.com/another-data', ['another_key' => 'another_value']]
        ];
    }

    #[DataProvider('invalidUrlProvider')]
    public function testGetWithInvalidUrl(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Failed to fetch data from URL/');

        $request = new Request('GET', $url);
        $this->clientMock
            ->shouldReceive('get')
            ->with($url)
            ->andThrow(new RequestException('Request error', $request));

        $this->httpClient->get($url);
    }

    public static function invalidUrlProvider(): array
    {
        return [
            ['https://invalid-url.com'],
            ['https://another-invalid-url.com']
        ];
    }
}

