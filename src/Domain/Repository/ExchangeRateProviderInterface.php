<?php
declare(strict_types=1);

namespace App\Domain\Repository;

interface ExchangeRateProviderInterface
{
    public function getRate(string $currency): string;
}
