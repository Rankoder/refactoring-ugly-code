<?php

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\Transaction;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * Class TransactionTest
 *
 * @package App\Tests\Domain\Entity
 */
class TransactionTest extends TestCase
{
    /**
     * Test the constructor and getters with valid data.
     */
    public function testValidTransaction(): void
    {
        $bin = '45717360';
        $amount = new Money(10000, new Currency('USD'));
        $currency = 'USD';

        $transaction = new Transaction($bin, $amount, $currency);

        $this->assertEquals($bin, $transaction->getBin());
        $this->assertEquals($amount, $transaction->getAmount());
        $this->assertEquals($currency, $transaction->getCurrency());
    }

    /**
     * Test the constructor with an invalid BIN.
     */
    public function testInvalidBin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid BIN provided.');

        $bin = 'invalid_bin';
        $amount = new Money(10000, new Currency('USD'));
        $currency = 'USD';

        new Transaction($bin, $amount, $currency);
    }

    /**
     * Test the constructor with an empty BIN.
     */
    public function testEmptyBin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid BIN provided.');

        $bin = '';
        $amount = new Money(10000, new Currency('USD'));
        $currency = 'USD';

        new Transaction($bin, $amount, $currency);
    }

    /**
     * Test the constructor with an invalid currency.
     */
    public function testInvalidCurrency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency provided.');

        $bin = '45717360';
        $amount = new Money(10000, new Currency('USD'));
        $currency = 'invalid_currency';

        new Transaction($bin, $amount, $currency);
    }

    /**
     * Test the constructor with an empty currency.
     */
    public function testEmptyCurrency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency provided.');

        $bin = '45717360';
        $amount = new Money(10000, new Currency('USD'));
        $currency = '';

        new Transaction($bin, $amount, $currency);
    }

    /**
     * Test the constructor with a currency that is not 3 characters long.
     */
    public function testShortCurrency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency provided.');

        $bin = '45717360';
        $amount = new Money(10000, new Currency('USD'));
        $currency = 'US';

        new Transaction($bin, $amount, $currency);
    }

    /**
     * Test the constructor with a valid BIN and currency, and check setting amount.
     */
    public function testValidAmount(): void
    {
        $bin = '45717360';
        $amount = new Money(10000, new Currency('USD'));
        $currency = 'USD';

        $transaction = new Transaction($bin, $amount, $currency);

        $this->assertEquals($amount, $transaction->getAmount());
    }
}
