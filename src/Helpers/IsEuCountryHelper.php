<?php
declare(strict_types=1);

namespace App\Helpers;

class IsEuCountryHelper
{
    private static array $euCountries = [];

    public static function loadConfig(string $configPath): void
    {
        if (file_exists($configPath)) {
            $config = include $configPath;
            if (isset($config['EU_COUNTRIES'])) {
                self::$euCountries = $config['EU_COUNTRIES'];
            } else {
                throw new \Exception("EU_COUNTRIES not found in configuration file.");
            }
        } else {
            throw new \Exception("Configuration file not found: $configPath");
        }
    }

    public static function isEu(string $countryCode): bool
    {
        return in_array($countryCode, self::$euCountries, true);
    }
}
