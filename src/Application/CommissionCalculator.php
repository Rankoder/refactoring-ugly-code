<?php

namespace App\Application;

use App\Domain\Entity\Transaction;
use App\Domain\Service\CommissionService;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

class CommissionCalculator
{
    private CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function processFile(string $filePath): void
    {
        $rows = explode("\n", file_get_contents($filePath));
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);
        $moneyParser = new DecimalMoneyParser($currencies);

        foreach ($rows as $row) {
            if (empty($row)) {
                continue;
            }

            $data = json_decode($row, true);
            if (!$data) {
                continue;
            }

            $bin = $data['bin'];
            $amount = new Money($data['amount'] * 100, new Currency($data['currency']));
            $currency = $data['currency'];

            $transaction = new Transaction($bin, $amount, $currency);
            $commission = $this->commissionService->calculateCommission($transaction);

            echo $moneyFormatter->format($commission) . PHP_EOL;
        }
    }
}
