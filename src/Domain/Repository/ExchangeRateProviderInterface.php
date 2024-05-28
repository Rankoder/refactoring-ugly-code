<?php

namespace App\Domain\Repository;

interface ExchangeRateProvider
{
    public function getRate(string $currency): float;
}
