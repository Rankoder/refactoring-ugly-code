<?php

namespace App\Domain\Entity;

use Money\Money;

class Transaction
{
    private string $bin;
    private Money $amount;
    private string $currency;

    public function __construct(string $bin, Money $amount, string $currency)
    {
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getBin(): string
    {
        return $this->bin;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
