<?php
/**
 * Release check test.
 */

$root = dirname(__DIR__);

$file =
    $root .
    '/upload/admin/controller/extension/ohbono/module/wallet_release.php';

if (!is_file($file)) {
    echo "FAIL: release controller missing.\n";
    exit(1);
}

$contents = file_get_contents($file);

foreach ([
    "'ledger'",
    "'cross_customer'",
    "'backup'",
    "'journal'"
] as $needle) {
    if (strpos($contents, $needle) === false) {
        echo "FAIL: release check missing {$needle}\n";
        exit(1);
    }

    echo "PASS: release check includes {$needle}\n";
}

echo "All OHBONO Wallet 0126 release checks passed.\n";
