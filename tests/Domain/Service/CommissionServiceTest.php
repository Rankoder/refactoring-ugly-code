<?php
declare(strict_types=1);

namespace App\Tests\Domain\Service;

use App\Domain\Entity\Transaction;
use App\Domain\Repository\BinListProviderInterface;
use App\Domain\Repository\ExchangeRateProviderInterface;
use App\Domain\Service\CommissionService;
use App\Helpers\IsEuCountryHelper;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * Class CommissionServiceTest
 *
 * Unit tests for the CommissionService class.
 */
class CommissionServiceTest extends TestCase
{
    private BinListProviderInterface $binListProvider;
    private ExchangeRateProviderInterface $exchangeRateProvider;
    private CommissionService $commissionService;

    /**
     * Set up the test environment.
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $this->binListProvider = $this->createMock(BinListProviderInterface::class);
        $this->exchangeRateProvider = $this->createMock(ExchangeRateProviderInterface::class);

        $this->commissionService = new CommissionService(
            $this->binListProvider,
            $this->exchangeRateProvider,
        );

        // Load EU countries configuration for tests
        $configPath = __DIR__ . '/../../../src/config/eu_countries.php';
        IsEuCountryHelper::loadConfig(realpath($configPath));
    }

    /**
     * Data provider for testCalculateCommission.
     *
     * @return array
     */
    public static function transactionProvider(): array
    {
        return [
            'Case 1: EUR transaction in EU' => ['45717360', '100.00', 'EUR', 'DE', '1.0', '1.00'],
            'Case 2: USD transaction in non-EU' => ['45717360', '100.00', 'USD', 'US', '1.0', '2.00'],
            'Case 3: USD transaction with rate 2.165' => ['516793', '50.00', 'USD', 'US', '2.165', '0.47'],
            'Case 4: JPY transaction' => ['45417360', '10000.00', 'JPY', 'JP', '130.0', '1.54'],
            'Case 5: USD transaction with rate 1.2' => ['41417360', '130.00', 'USD', 'US', '1.2', '2.17'],
            'Case 6: GBP transaction' => ['4745030', '2000.00', 'GBP', 'GB', '0.9', '44.45'],
            'Case 7: Zero amount' => ['45717360', '0.00', 'EUR', 'DE', '1.0', '0.00'],
            'Case 8: Negative amount' => ['45717360', '-100.00', 'EUR', 'DE', '1.0', '-1.00'],
            'Case 9: Very large amount' => ['45717360', '999999999999.99', 'EUR', 'DE', '1.0', '10000000000.00'],
            'Case 10: Very small exchange rate' => ['45717360', '0.01', 'USD', 'US', '0.01', '0.02'],
            'Case 11: Extremely small exchange rate' => ['45717360', '100.00', 'USD', 'US', '0.0001', '20000.00'],
        ];
    }

    /**
     * Test calculateCommission method.
     *
     * @param string $bin
     * @param string $amount
     * @param string $currency
     * @param string $countryCode
     * @param string $rate
     * @param string $expectedCommission
     *
     * @return void
     *
     */
    #[DataProvider('transactionProvider')]
    public function testCalculateCommission(
        string $bin,
        string $amount,
        string $currency,
        string $countryCode,
        string $rate,
        string $expectedCommission
    ): void
    {
        $this->binListProvider
            ->method('getCountryCode')
            ->willReturn($countryCode);

        $this->exchangeRateProvider
            ->method('getRate')
            ->willReturn($rate);

        $transaction = new Transaction($bin, new Money((int)($amount * 100), new Currency($currency)), $currency);
        $commission = $this->commissionService->calculateCommission($transaction);

        $this->assertEquals($expectedCommission, $commission);
    }

    /**
     * Data provider for testCalculateCommissionWithInvalidRatio.
     *
     * @return array
     */
    public static function invalidRatioProvider(): array
    {
        return [
            'Case 1: Exchange rate with more than 4 decimal places' => ['45717360', '100.00', 'USD', 'US', '0.00001'],
            'Case 2: Exchange rate with more than 4 decimal places (2)' => ['45717360', '100.00', 'USD', 'US', '0.123456'],
        ];
    }

    /**
     * Test calculateCommission with invalid ratios.
     *
     * @param string $bin
     * @param string $amount
     * @param string $currency
     * @param string $countryCode
     * @param string $rate
     *
     * @return void
     *
     */
    #[DataProvider('invalidRatioProvider')]
    public function testCalculateCommissionWithInvalidRatio(
        string $bin,
        string $amount,
        string $currency,
        string $countryCode,
        string $rate
    ): void
    {
        $this->binListProvider
            ->method('getCountryCode')
            ->willReturn($countryCode);

        $this->exchangeRateProvider
            ->method('getRate')
            ->willReturn($rate);

        $this->expectException(InvalidArgumentException::class);

        $transaction = new Transaction($bin, new Money((int)($amount * 100), new Currency($currency)), $currency);
        $this->commissionService->calculateCommission($transaction);
    }
}
