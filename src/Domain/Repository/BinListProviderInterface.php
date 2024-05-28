<?php

namespace App\Domain\Repository;

/**
 * Interface BinListProviderInterface
 *
 * Provides the country code for a given BIN.
 *
 * @package App\Domain\Repository
 */
interface BinListProviderInterface
{
    /**
     * Get the country code for a given BIN.
     *
     * @param string $bin The BIN to look up.
     *
     * @return string The country code associated with the BIN.
     */
    public function getCountryCode(string $bin): string;
}
