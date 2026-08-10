<?php
/**
 * Runtime tests for the framework-independent checkout callback guard.
 */

require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_checkout_callback_guard.php';

$guard = new \OhbonoWalletCheckoutCallbackGuard();

$guard->validateOrder([
    'order_id' => 1001,
    'customer_id' => 25,
    'total' => 499.99
], 25);

echo "PASS: valid customer/order pair accepted.\n";

$failed = false;

try {
    $guard->validateOrder([
        'order_id' => 1001,
        'customer_id' => 26,
        'total' => 499.99
    ], 25);
} catch (\RuntimeException $e) {
    $failed = true;
}

if (!$failed) {
    echo "FAIL: customer ownership was not enforced.\n";
    exit(1);
}

echo "PASS: customer ownership is enforced.\n";

echo "All OHBONO Wallet 0114 callback guard checks passed.\n";
