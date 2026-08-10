<?php
/**
 * Environment/integration static checks.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/admin/model/extension/ohbono/module/wallet_environment.php',
        'SHOW TABLES LIKE'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_environment.php',
        'opencart_version'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_journal.php',
        'formatted_balance'
    ],
    [
        $root . '/upload/catalog/view/javascript/ohbono/wallet-journal.js',
        'ohbono:wallet:updated'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_checkout_callback_guard.php',
        'Order does not belong to customer'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_checkout_callback.php',
        'WalletCheckoutCallbackGuard'
    ]
];

$failed = 0;

foreach ($checks as [$file, $needle]) {
    if (!is_file($file)) {
        echo "FAIL: Missing {$file}\n";
        $failed++;
        continue;
    }

    if (strpos(file_get_contents($file), $needle) === false) {
        echo "FAIL: {$needle} not found in {$file}\n";
        $failed++;
        continue;
    }

    echo "PASS: {$file} contains {$needle}\n";
}

if ($failed) {
    exit(1);
}

echo "All OHBONO Wallet 0112–0114 static checks passed.\n";
