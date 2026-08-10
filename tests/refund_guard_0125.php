<?php
/**
 * Static verification for refund hardening.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/system/library/ohbono/wallet_refund_guard.php',
        'already been processed'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_atomic_capture.php',
        'FOR UPDATE'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_customer_order_guard.php',
        'does not belong to customer'
    ]
];

foreach ($checks as [$file, $needle]) {
    if (!is_file($file)) {
        echo "FAIL: Missing {$file}\n";
        exit(1);
    }

    if (strpos(file_get_contents($file), $needle) === false) {
        echo "FAIL: {$needle} not found.\n";
        exit(1);
    }

    echo "PASS: {$needle}\n";
}

echo "All OHBONO Wallet 0125 refund/locking checks passed.\n";
