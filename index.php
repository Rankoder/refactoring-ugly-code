<?php

require __DIR__ . '/vendor/autoload.php';

use App\Application\CommissionCalculator;
use App\Domain\Service\CommissionService;
use App\Helpers\IsEuCountryHelper;
use App\Infrastructure\BinListProvider;
use App\Infrastructure\ExchangeRateProvider;
use App\Infrastructure\HttpClient;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\DecimalMoneyParser;

// Load EU countries configuration
$configPath = __DIR__ . '/config/eu_countries.php';
try {
    IsEuCountryHelper::loadConfig($configPath);
} catch (Exception $e) {
}

$httpClient = new HttpClient();
$binListProvider = new BinListProvider($httpClient);
$exchangeRateProvider = new ExchangeRateProvider($httpClient);
$currencies = new ISOCurrencies();
$moneyFormatter = new DecimalMoneyFormatter($currencies);
$moneyParser = new DecimalMoneyParser($currencies);

$commissionService = new CommissionService($binListProvider, $exchangeRateProvider, $moneyFormatter, $moneyParser);
$commissionCalculator = new CommissionCalculator($commissionService);

$inputFilePath = $argv[1] ?? 'input.txt';

if (!file_exists($inputFilePath)) {
    echo "File not found: $inputFilePath" . PHP_EOL;
    exit(1);
}

$commissionCalculator->processFile($inputFilePath);
