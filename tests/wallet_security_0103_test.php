<?php
/**
 * Dependency-free static checks for Batch 0103–0105.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/system/library/ohbono/wallet_security.php',
        'validateReference'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_reference_service.php',
        'OBW-PAY-'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_schema_validator.php',
        'wallet_payment_state'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_refund.php',
        'REQUEST_METHOD'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_payment.php',
        'ReferenceService'
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

echo "All OHBONO Wallet 0103–0105 static checks passed.\n";
