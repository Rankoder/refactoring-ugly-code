<?php

namespace App\Tests\Integration;

use App\Application\CommissionCalculator;
use App\Domain\Repository\BinListProviderInterface;
use App\Domain\Repository\ExchangeRateProviderInterface;
use App\Domain\Service\CommissionService;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class CommissionCalculatorIntegrationTest
 *
 * @package App\Tests\Integration
 */
class CommissionCalculatorIntegrationTest extends TestCase
{
    private $binListProviderMock;
    private $exchangeRateProviderMock;
    private $commissionService;
    private $commissionCalculator;

    protected function setUp(): void
    {
        $this->binListProviderMock = Mockery::mock(BinListProviderInterface::class);
        $this->exchangeRateProviderMock = Mockery::mock(ExchangeRateProviderInterface::class);
        $this->commissionService = new CommissionService(
            $this->binListProviderMock,
            $this->exchangeRateProviderMock
        );
        $this->commissionCalculator = new CommissionCalculator($this->commissionService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Data provider for testProcessFile.
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            'EUR transaction' => [
                'data' => '{"bin":"45717360","amount":"100.00","currency":"EUR"}',
                'bin' => '45717360',
                'countryCode' => 'DE',
                'currency' => 'EUR',
                'exchangeRate' => '1.0',
                'expectedOutput' => "1.00\n"
            ],
            'USD transaction' => [
                'data' => '{"bin":"516793","amount":"50.00","currency":"USD"}',
                'bin' => '516793',
                'countryCode' => 'US',
                'currency' => 'USD',
                'exchangeRate' => '1.2',
                'expectedOutput' => "0.84\n"
            ],
            'JPY transaction' => [
                'data' => '{"bin":"45417360","amount":"10000.00","currency":"JPY"}',
                'bin' => '45417360',
                'countryCode' => 'JP',
                'currency' => 'JPY',
                'exchangeRate' => '130.0',
                'expectedOutput' => "1.54\n"
            ],
            'USD transaction 2' => [
                'data' => '{"bin":"41417360","amount":"130.00","currency":"USD"}',
                'bin' => '41417360',
                'countryCode' => 'US',
                'currency' => 'USD',
                'exchangeRate' => '1.2',
                'expectedOutput' => "2.17\n"
            ],
            'GBP transaction' => [
                'data' => '{"bin":"4745030","amount":"2000.00","currency":"GBP"}',
                'bin' => '4745030',
                'countryCode' => 'GB',
                'currency' => 'GBP',
                'exchangeRate' => '0.9',
                'expectedOutput' => "44.45\n"
            ],
        ];
    }

    /**
     * Test the processFile method with various data.
     *
     *
     * @param string $data The JSON data for the transaction.
     * @param string $bin The BIN number.
     * @param string $countryCode The country code for the BIN.
     * @param string $currency The currency of the transaction.
     * @param string $exchangeRate The exchange rate for the currency.
     * @param string $expectedOutput The expected output after processing the file.
     *
     * @return void
     */
    #[DataProvider('dataProvider')]
    public function testProcessFile(
        string $data,
        string $bin,
        string $countryCode,
        string $currency,
        string $exchangeRate,
        string $expectedOutput
    ): void {
        $this->binListProviderMock
            ->shouldReceive('getCountryCode')
            ->with($bin)
            ->andReturn($countryCode);

        $this->exchangeRateProviderMock
            ->shouldReceive('getRate')
            ->with($currency)
            ->andReturn($exchangeRate);

        $filePath = __DIR__ . '/data/transactions.txt';
        file_put_contents($filePath, $data);

        ob_start();
        $this->commissionCalculator->processFile($filePath);
        $output = ob_get_clean();

        $normalizedOutput = str_replace("\r\n", "\n", $output);

        $this->assertEquals($expectedOutput, $normalizedOutput);

        unlink($filePath);
    }
}
