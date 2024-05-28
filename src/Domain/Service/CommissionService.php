<?php

namespace App\Domain\Service;

use App\Domain\Entity\Transaction;
use App\Domain\Repository\BinListProvider;
use App\Domain\Repository\ExchangeRateProvider;
use App\Helpers\IsEuCountryHelper;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;
use Money\MoneyFormatter;
use Money\Parser\DecimalMoneyParser;

class CommissionService
{
    private BinListProvider $binListProvider;
    private ExchangeRateProvider $exchangeRateProvider;
    private MoneyFormatter $moneyFormatter;
    private DecimalMoneyParser $moneyParser;

    public function __construct(
        BinListProvider $binListProvider,
        ExchangeRateProvider $exchangeRateProvider,
        MoneyFormatter $moneyFormatter,
        DecimalMoneyParser $moneyParser
    ) {
        $this->binListProvider = $binListProvider;
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->moneyFormatter = $moneyFormatter;
        $this->moneyParser = $moneyParser;
    }

    public function calculateCommission(Transaction $transaction): Money
    {
        $countryCode = $this->binListProvider->getCountryCode($transaction->getBin());
        $isEu = IsEuCountryHelper::isEu($countryCode);

        $exchangeRate = $this->exchangeRateProvider->getRate($transaction->getCurrency());
        $amountInEur = $transaction->getAmount()->divide($exchangeRate);

        $commissionRate = $isEu ? 0.01 : 0.02;
        $commission = $amountInEur->multiply($commissionRate);

        // Ceiling the commission to cents
        $commissionAmount = ceil($this->moneyParser->parse((string)$commission->getAmount(), new Currency('EUR'))->getAmount() / 100) * 100;
        $commission = new Money($commissionAmount, new Currency('EUR'));

        return $commission;
    }
}
