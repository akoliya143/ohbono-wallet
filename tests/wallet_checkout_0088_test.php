<?php
/**
 * Dependency-free static checks for Batch 0088–0090.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/catalog/model/extension/ohbono/module/wallet_checkout.php',
        'calculateUsage'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_checkout.php',
        'isLogged'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_checkout_totals.php',
        'remaining_total'
    ],
    [
        $root . '/upload/catalog/view/template/extension/ohbono/module/wallet_checkout.twig',
        'ohbono-wallet-amount'
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

echo "All OHBONO Wallet 0088–0090 static checks passed.\n";
