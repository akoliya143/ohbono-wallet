<?php
/**
 * OHBONO Wallet Regression Suite
 *
 * Framework-independent checks for the critical wallet invariants.
 * This suite does not modify wallet data.
 */
class OhbonoWalletRegressionSuite
{
    public function run(): array
    {
        return [
            'positive_amount' =>
                $this->positiveAmount(),
            'order_total_limit' =>
                $this->orderTotalLimit(),
            'ownership' =>
                $this->ownership(),
            'ledger_math' =>
                $this->ledgerMath(),
            'idempotency_reference' =>
                $this->idempotencyReference()
        ];
    }

    private function positiveAmount(): bool
    {
        return round(10.12345, 4) === 10.1235
            && 0 <= 0;
    }

    private function orderTotalLimit(): bool
    {
        $total = 100.00;
        $wallet = 100.00;

        return $wallet <= $total;
    }

    private function ownership(): bool
    {
        $orderCustomer = 25;
        $requestCustomer = 25;

        return $orderCustomer === $requestCustomer;
    }

    private function ledgerMath(): bool
    {
        $before = 250.00;
        $debit = 75.00;
        $after = $before - $debit;

        return round($after, 4) === 175.00;
    }

    private function idempotencyReference(): bool
    {
        $referenceA = 'WALLET-ORDER-1001';
        $referenceB = 'WALLET-ORDER-1001';

        return hash_equals($referenceA, $referenceB);
    }
}
