<?php
/**
 * Dependency-free static checks for Batch 0097–0099.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/system/library/ohbono/wallet_reversal_service.php',
        'wallet_reversal'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_reversal_service.php',
        'START TRANSACTION'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_reversal_service.php',
        'FOR UPDATE'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_refund_service.php',
        'refundOrderWalletPayment'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_payment_state_service.php',
        'wallet_capture_requires_reconciliation'
    ],
    [
        $root . '/upload/admin/model/extension/ohbono/module/wallet_reconciliation.php',
        'wallet_reversal'
    ]
];

$failed = 0;

foreach ($checks as [$file, $needle]) {
    if (!is_file($file)) {
        echo "FAIL: Missing {$file}\n";
        $failed++;
        continue;
    }

    $contents = file_get_contents($file);

    if (strpos($contents, $needle) === false) {
        echo "FAIL: {$needle} not found in {$file}\n";
        $failed++;
        continue;
    }

    echo "PASS: {$file} contains {$needle}\n";
}

if ($failed > 0) {
    exit(1);
}

echo "All OHBONO Wallet 0097–0099 static checks passed.\n";
