<?php
/**
 * Dependency-free static checks for Batch 0094–0096.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/system/library/ohbono/wallet_order_payment_service.php',
        'getOrder'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_order_payment_service.php',
        'Order total changed'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_partial_payment_service.php',
        'remaining_amount'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_order_payment.php',
        'model_checkout_order'
    ],
    [
        $root . '/upload/admin/model/extension/ohbono/module/wallet_transaction.php',
        'getOrderWalletPayment'
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

echo "All OHBONO Wallet 0094–0096 static checks passed.\n";
