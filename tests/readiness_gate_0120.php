<?php
/**
 * Runtime tests for the production readiness gate.
 */

require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_readiness_gate.php';

$gate = new \OhbonoWalletReadinessGate();

$not_ready = $gate->evaluate([
    'database_schema' => true,
    'admin_permissions' => true
]);

if ($not_ready['ready'] !== false ||
    !$not_ready['missing']) {
    echo "FAIL: incomplete readiness must not pass.\n";
    exit(1);
}

echo "PASS: incomplete readiness is rejected.\n";

$checks = [
    'database_schema' => true,
    'admin_permissions' => true,
    'wallet_capture_idempotency' => true,
    'refund_reversal' => true,
    'journal_checkout_verified' => true,
    'staging_wallet_only' => true,
    'staging_partial_wallet' => true,
    'staging_failure_reconciliation' => true,
    'production_backup' => true
];

$ready = $gate->evaluate($checks);

if ($ready['ready'] !== true ||
    $ready['missing']) {
    echo "FAIL: complete readiness must pass.\n";
    exit(1);
}

echo "PASS: complete readiness is accepted.\n";
echo "All OHBONO Wallet 0120 readiness checks passed.\n";
