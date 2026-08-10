<?php
/**
 * Production package integrity check.
 */

$root = dirname(__DIR__);

$required = [
    'extension.json',
    'docs/README-0130-0132.md',
    'upload/system/library/ohbono/wallet_release_report.php',
    'upload/system/library/ohbono/wallet_evidence.php',
    'upload/admin/controller/extension/ohbono/module/wallet_release_report.php'
];

foreach ($required as $file) {
    if (!is_file($root . '/' . $file)) {
        echo "FAIL: missing {$file}\n";
        exit(1);
    }

    echo "PASS: {$file}\n";
}

echo "Production package integrity checks passed.\n";
