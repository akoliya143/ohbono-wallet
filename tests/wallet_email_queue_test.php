<?php
/**
 * Lightweight static test checklist for Batch 0073–0075.
 *
 * These checks are intentionally dependency-free. They validate that the
 * expected queue implementation files exist and contain critical safeguards.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/system/library/ohbono/wallet_email_worker.php',
        'recoverStaleProcessing'
    ],
    [
        $root . '/upload/cron/wallet_email_worker.php',
        'flock'
    ],
    [
        $root . '/upload/admin/model/extension/ohbono/module/wallet_email_queue.php',
        'getStats'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_email_queue.php',
        'retry'
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

    echo "PASS: {$file}\n";
}

if ($failed > 0) {
    exit(1);
}

echo "All OHBONO Wallet 0073–0075 static checks passed.\n";
