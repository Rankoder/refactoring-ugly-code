<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use Money\Money;
use InvalidArgumentException;

/**
 * Class Transaction
 *
 * Represents a financial transaction.
 *
 * @package App\Domain\Entity
 */
class Transaction
{
    /**
     * @var string The BIN (Bank Identification Number) of the transaction.
     */
    private string $bin;

    /**
     * @var Money The amount of the transaction.
     */
    private Money $amount;

    /**
     * @var string The currency of the transaction.
     */
    private string $currency;

    /**
     * Transaction constructor.
     *
     * @param string $bin The BIN of the transaction.
     * @param Money $amount The amount of the transaction.
     * @param string $currency The currency of the transaction.
     *
     * @throws InvalidArgumentException if any of the arguments are invalid.
     */
    public function __construct(string $bin, Money $amount, string $currency)
    {
        $this->setBin($bin);
        $this->amount = $amount;
        $this->setCurrency($currency);
    }

    /**
     * Get the BIN of the transaction.
     *
     * @return string The BIN of the transaction.
     */
    public function getBin(): string
    {
        return $this->bin;
    }

    /**
     * Set the BIN of the transaction.
     *
     * @param string $bin The BIN of the transaction.
     *
     * @throws InvalidArgumentException if the BIN is invalid.
     */
    private function setBin(string $bin): void
    {
        if (empty($bin) || !is_numeric($bin)) {
            throw new InvalidArgumentException('Invalid BIN provided.');
        }

        $this->bin = $bin;
    }

    /**
     * Get the amount of the transaction.
     *
     * @return Money The amount of the transaction.
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }

    /**
     * Get the currency of the transaction.
     *
     * @return string The currency of the transaction.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Set the currency of the transaction.
     *
     * @param string $currency The currency of the transaction.
     *
     * @throws InvalidArgumentException if the currency is invalid.
     */
    private function setCurrency(string $currency): void
    {
        if (empty($currency) || strlen($currency) !== 3) {
            throw new InvalidArgumentException('Invalid currency provided.');
        }

        $this->currency = $currency;
    }
}
