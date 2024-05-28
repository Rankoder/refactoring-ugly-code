<?php

namespace App\Domain\Repository;

interface ExchangeRateProviderInterface
{
    public function getRate(string $currency): string;
}
