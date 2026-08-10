<?php
/**
 * Static checks for observability/readiness.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/system/library/ohbono/wallet_observability.php',
        'wallet_operation_log'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_callback_guard.php',
        'findExisting'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_readiness_gate.php',
        'journal_checkout_verified'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_operations.php',
        'wallet_operation_log'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_readiness.php',
        'OhbonoWalletReadinessGate'
    ]
];

foreach ($checks as [$file, $needle]) {
    if (!is_file($file)) {
        echo "FAIL: Missing {$file}\n";
        exit(1);
    }

    if (strpos(file_get_contents($file), $needle) === false) {
        echo "FAIL: {$needle} not found in {$file}\n";
        exit(1);
    }

    echo "PASS: {$needle}\n";
}

echo "All OHBONO Wallet 0118–0120 static checks passed.\n";
