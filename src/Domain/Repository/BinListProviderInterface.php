<?php

namespace App\Domain\Repository;

interface BinListProvider
{
    public function getCountryCode(string $bin): string;
}
