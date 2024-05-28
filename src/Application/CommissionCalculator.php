<?php

namespace App\Application;

use App\Domain\Entity\Transaction;
use App\Domain\Service\CommissionService;
use Money\Currency;
use Money\Money;
use InvalidArgumentException;

/**
 * Class CommissionCalculator
 *
 * Handles the processing of a file containing transaction data and calculates the commission for each transaction.
 */
class CommissionCalculator
{
    private CommissionService $commissionService;

    /**
     * CommissionCalculator constructor.
     *
     * @param CommissionService $commissionService The service responsible for calculating commission.
     */
    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Processes the file containing transaction data.
     *
     * @param string $filePath The path to the file containing the transaction data.
     *
     * @throws InvalidArgumentException If the file cannot be read.
     */
    public function processFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: $filePath");
        }

        $rows = explode("\n", file_get_contents($filePath));

        foreach ($rows as $row) {
            if (empty(trim($row))) {
                continue;
            }

            $data = json_decode($row, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            try {
                $bin = $data['bin'];
                $amount = new Money((string) ((float)$data['amount'] * 100), new Currency($data['currency']));
                $currency = $data['currency'];

                $transaction = new Transaction($bin, $amount, $currency);
                $commission = $this->commissionService->calculateCommission($transaction);

                echo $commission . PHP_EOL;
            } catch (\Exception $e) {
                // Log or handle the exception as needed
                continue;
            }
        }
    }
}
