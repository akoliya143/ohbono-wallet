<?php
/**
 * Static checks for confirmed staging wiring.
 */

$root = dirname(__DIR__);

$checks = [
    [
        $root . '/upload/admin/model/extension/ohbono/module/wallet_event_registry.php',
        'INSERT INTO'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_event_install.php',
        'registered_event_ids'
    ],
    [
        $root . '/upload/catalog/controller/extension/ohbono/module/wallet_checkout_confirm.php',
        'validateOrder'
    ],
    [
        $root . '/upload/catalog/view/javascript/ohbono/wallet-journal-checkout.js',
        'getPayload'
    ]
];

foreach ($checks as [$file, $needle]) {
    if (!is_file($file)) {
        echo "FAIL: Missing {$file}\n";
        exit(1);
    }

    if (strpos(file_get_contents($file), $needle) === false) {
        echo "FAIL: {$needle} not found in {$file}\n";
        exit(1);
    }

    echo "PASS: {$needle}\n";
}

echo "All OHBONO Wallet 0115–0117 wiring checks passed.\n";
