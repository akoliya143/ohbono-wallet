<?php
/**
 * Dependency-free static checks for Batch 0091–0093.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/system/library/ohbono/wallet_payment_service.php',
        'FOR UPDATE'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_payment_service.php',
        'wallet_payment'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_payment_service.php',
        'START TRANSACTION'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_payment.php',
        'isLogged'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_payment_validator.php',
        'exceeds order total'
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

echo "All OHBONO Wallet 0091–0093 static checks passed.\n";
