<?php
/**
 * Dependency-free static checks for Batch 0085–0087.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/catalog/model/extension/ohbono/module/wallet_history.php',
        'getTotalTransactions'
    ],
    [
        $root . '/upload/catalog/model/extension/ohbono/module/wallet_history.php',
        'customer_id'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_history.php',
        'getTransaction'
    ],
    [
        $root . '/upload/catalog/view/template/extension/ohbono/module/wallet_history.twig',
        'pagination'
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

echo "All OHBONO Wallet 0085–0087 static checks passed.\n";
