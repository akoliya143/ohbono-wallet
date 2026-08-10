<?php
require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_release_report.php';

$report = new \OhbonoWalletReleaseReport();

$result = $report->build([
    'wallet_only' => 'pass'
]);

if ($result['ready'] !== false ||
    !$result['missing']) {
    echo "FAIL: incomplete evidence must not release.\n";
    exit(1);
}

echo "PASS: incomplete evidence blocks release.\n";

$all = [];

foreach ([
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
] as $scenario) {
    $all[$scenario] = 'pass';
}

$result = $report->build($all);

if ($result['ready'] !== true ||
    $result['missing'] ||
    $result['failed']) {
    echo "FAIL: complete evidence should pass.\n";
    exit(1);
}

echo "PASS: complete evidence can release.\n";
echo "All OHBONO Wallet 0130 evidence checks passed.\n";
