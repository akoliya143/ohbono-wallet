<?php
/**
 * Dependency-free static checks for Batch 0100–0102.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/catalog/model/extension/ohbono/module/wallet_refund_history.php',
        'wallet_reversal'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_payment_state.php',
        'customer_id'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_payment_state_store.php',
        'reconciliation_required'
    ],
    [
        $root . '/upload/admin/model/extension/ohbono/module/wallet_reconciliation.php',
        'wallet_payment_state'
    ],
    [
        $root . '/upload/admin/view/template/extension/ohbono/module/wallet_reconciliation.twig',
        'No wallet funds are automatically reversed'
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

echo "All OHBONO Wallet 0100–0102 static checks passed.\n";
