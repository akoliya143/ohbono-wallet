<?php
/**
 * Framework-independent event definition checks.
 */

$root = dirname(__DIR__);

$files = [
    $root . '/upload/admin/model/extension/ohbono/module/wallet_event_installer.php',
    $root . '/upload/catalog/controller/extension/ohbono/module/wallet_event.php',
    $root . '/upload/catalog/controller/event/ohbono_wallet.php'
];

foreach ($files as $file) {
    if (!is_file($file)) {
        echo "FAIL: Missing {$file}\n";
        exit(1);
    }

    echo "PASS: Found {$file}\n";
}

$contents = file_get_contents(
    $root . '/upload/admin/model/extension/ohbono/module/wallet_event_installer.php'
);

foreach ([
    'ohbono_wallet_checkout_before',
    'ohbono_wallet_order_after',
    'checkoutBefore',
    'orderAfter'
] as $needle) {
    if (strpos($contents, $needle) === false) {
        echo "FAIL: Missing event definition {$needle}\n";
        exit(1);
    }

    echo "PASS: Event definition {$needle}\n";
}

echo "All OHBONO Wallet 0109–0111 event checks passed.\n";
