<?php

namespace App\Tests\Application;

use App\Application\CommissionCalculator;
use App\Domain\Entity\Transaction;
use App\Domain\Service\CommissionService;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Mockery;
use InvalidArgumentException;

/**
 * Class CommissionCalculatorTest
 *
 * Unit tests for the CommissionCalculator class.
 */
class CommissionCalculatorTest extends TestCase
{
    private CommissionService $commissionService;
    private CommissionCalculator $commissionCalculator;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->commissionService = Mockery::mock(CommissionService::class);
        $this->commissionCalculator = new CommissionCalculator($this->commissionService);
    }

    /**
     * Tear down the test environment.
     */
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
            'Valid transaction' => [
                'fileContent' => '{"bin":"45717360","amount":"100.00","currency":"EUR"}',
                'expectedOutput' => '1.00' . PHP_EOL,
                'transactions' => [
                    [
                        'bin' => '45717360',
                        'amount' => new Money('10000', new Currency('EUR')),
                        'currency' => 'EUR',
                        'commission' => '1.00'
                    ]
                ]
            ],
            'Invalid JSON' => [
                'fileContent' => 'invalid json',
                'expectedOutput' => '',
                'transactions' => []
            ],
            'Empty line' => [
                'fileContent' => '',
                'expectedOutput' => '',
                'transactions' => []
            ],
        ];
    }

    /**
     * Test processFile method.
     *
     * @param string $fileContent
     * @param string $expectedOutput
     * @param array $transactions
     *
     * @return void
     *
     */
    #[DataProvider('dataProvider')]
    public function testProcessFile(string $fileContent, string $expectedOutput, array $transactions): void
    {
        // Prepare a temporary file
        $filePath = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($filePath, $fileContent);

        // Mock the CommissionService
        foreach ($transactions as $transactionData) {
            $transaction = new Transaction(
                $transactionData['bin'],
                $transactionData['amount'],
                $transactionData['currency']
            );
            $this->commissionService
                ->shouldReceive('calculateCommission')
                ->with(Mockery::on(function ($arg) use ($transaction) {
                    return $arg->getBin() === $transaction->getBin() &&
                        $arg->getAmount()->equals($transaction->getAmount()) &&
                        $arg->getCurrency() === $transaction->getCurrency();
                }))
                ->andReturn($transactionData['commission']);
        }

        // Capture the output
        ob_start();
        $this->commissionCalculator->processFile($filePath);
        $output = ob_get_clean();

        // Assertions
        $this->assertEquals($expectedOutput, $output);

        // Clean up the temporary file
        unlink($filePath);
    }

    /**
     * Test processFile with a non-existent file.
     */
    public function testProcessFileWithNonExistentFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found: non_existent_file.txt');
        $this->commissionCalculator->processFile('non_existent_file.txt');
    }
}
