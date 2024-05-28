<?php

namespace App\Domain\Service;

use App\Domain\Entity\Transaction;
use App\Domain\Repository\BinListProviderInterface;
use App\Domain\Repository\ExchangeRateProviderInterface;
use App\Helpers\IsEuCountryHelper;
use InvalidArgumentException;
use Money\Money;

/**
 * Class CommissionService
 *
 * Service responsible for calculating commission fees for transactions.
 */
class CommissionService
{
    private BinListProviderInterface $binListProvider;
    private ExchangeRateProviderInterface $exchangeRateProvider;

    /**
     * CommissionService constructor.
     *
     * @param BinListProviderInterface $binListProvider The provider for BIN information.
     * @param ExchangeRateProviderInterface $exchangeRateProvider The provider for exchange rates.
     */
    public function __construct(
        BinListProviderInterface $binListProvider,
        ExchangeRateProviderInterface $exchangeRateProvider
    ) {
        $this->binListProvider = $binListProvider;
        $this->exchangeRateProvider = $exchangeRateProvider;
    }

    /**
     * Calculates the commission for a given transaction.
     *
     * @param Transaction $transaction The transaction for which the commission is calculated.
     *
     * @return string The calculated commission formatted as a string.
     *
     * @throws InvalidArgumentException If the exchange rate has more than 4 decimal places.
     */
    public function calculateCommission(Transaction $transaction): string
    {
        $countryCode = $this->binListProvider->getCountryCode($transaction->getBin());
        $isEu = IsEuCountryHelper::isEu($countryCode);

        $exchangeRate = $this->exchangeRateProvider->getRate($transaction->getCurrency());
        $this->validateExchangeRate($exchangeRate);

        $amountInEur = $this->convertToEur($transaction, $exchangeRate);

        $commissionRate = $isEu ? '0.01' : '0.02';
        $commission = $amountInEur->multiply((string)($commissionRate * 100), Money::ROUND_UP);

        // Round the commission to the nearest cent
        $commissionAmount = ceil($commission->getAmount() / 100);

        // Format the commission amount to 2 decimal places
        return number_format($commissionAmount / 100, 2, '.', '');
    }

    /**
     * Validates the exchange rate to ensure it does not have more than 4 decimal places.
     *
     * @param string $exchangeRate The exchange rate to validate.
     *
     * @throws InvalidArgumentException If the exchange rate has more than 4 decimal places.
     */
    private function validateExchangeRate(string $exchangeRate): void
    {
        if (str_contains($exchangeRate, '.') && strlen(substr(strrchr($exchangeRate, "."), 1)) > 4) {
            throw new InvalidArgumentException("Ratio $exchangeRate has more than 4 decimal places");
        }
    }

    /**
     * Converts the given amount to EUR based on the exchange rate.
     *
     * @param Transaction $transaction The transaction containing the amount to convert.
     * @param string $exchangeRate The exchange rate to use for conversion.
     *
     * @return Money The amount converted to EUR.
     */
    private function convertToEur(Transaction $transaction, string $exchangeRate): Money
    {
        if ($transaction->getCurrency() === 'EUR') {
            return $transaction->getAmount();
        }

        return $transaction->getAmount()->divide($exchangeRate);
    }
}
