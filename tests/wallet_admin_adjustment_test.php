<?php
/**
 * Dependency-free static checks for Batch 0079–0081.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/system/library/ohbono/wallet_admin_adjustment_service.php',
        'wallet_admin_audit'
    ],
    [
        $root . '/upload/system/library/ohbono/wallet_admin_adjustment_service.php',
        'FOR UPDATE'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_adjustment.php',
        'hasPermission'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_adjustment.php',
        'reason'
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

if ($failed) {
    exit(1);
}

echo "All OHBONO Wallet 0079–0081 static checks passed.\n";
