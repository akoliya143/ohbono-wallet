<?php
/**
 * Staging matrix.
 *
 * This describes required verification; it does not claim that the scenarios
 * have actually been executed.
 */

$required = [
    'wallet_only',
    'partial_wallet_external_success',
    'partial_wallet_external_failure',
    'insufficient_wallet_balance',
    'wallet_above_order_total',
    'duplicate_callback',
    'browser_refresh',
    'refund_after_paid_order',
    'reversal_after_failed_external_payment',
    'cross_customer_order_protection'
];

foreach ($required as $scenario) {
    echo "REQUIRED: {$scenario}\n";
}

echo "Staging matrix generated. No scenario is marked PASS automatically.\n";
