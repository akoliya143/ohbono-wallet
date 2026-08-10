<?php
/**
 * OHBONO Wallet Release Report
 *
 * Builds a conservative release report from supplied evidence.
 */
class OhbonoWalletReleaseReport
{
    private $required;

    public function __construct()
    {
        $this->required = [
            'wallet_only',
            'partial_wallet_external_success',
            'partial_wallet_external_failure',
            'insufficient_wallet_balance',
            'wallet_above_order_total',
            'duplicate_callback',
            'browser_refresh',
            'refund_after_paid_order',
            'reversal_after_failed_external_payment',
            'cross_customer_order_protection',
            'ledger_reconciliation',
            'journal_checkout'
        ];
    }

    public function build(array $results): array
    {
        $missing = [];
        $failed = [];

        foreach ($this->required as $scenario) {
            $result = $results[$scenario] ?? null;

            if ($result === null ||
                $result === '' ||
                $result === 'not_run') {
                $missing[] = $scenario;
                continue;
            }

            if ($result !== 'pass') {
                $failed[] = $scenario;
            }
        }

        return [
            'ready' =>
                !$missing && !$failed,
            'missing' => $missing,
            'failed' => $failed,
            'required_count' =>
                count($this->required),
            'passed_count' =>
                count($this->required) -
                count($missing) -
                count($failed)
        ];
    }
}
