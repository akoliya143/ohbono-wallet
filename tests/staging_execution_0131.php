<?php
/**
 * Human-execution checklist.
 *
 * This script only prints required scenarios. It intentionally never records
 * a PASS automatically.
 */

$scenarios = [
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

foreach ($scenarios as $scenario) {
    echo "RUN MANUALLY: {$scenario}\n";
}

echo "No staging scenario was automatically marked as PASS.\n";
