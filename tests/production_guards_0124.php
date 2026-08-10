<?php
/**
 * Runtime tests for production guards.
 */

require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_production_guard.php';

$guard = new \OhbonoWalletProductionGuard();

if ($guard->assertPositiveAmount(10.12345) !== 10.1235) {
    echo "FAIL: amount normalization.\n";
    exit(1);
}

echo "PASS: positive amount normalization.\n";

$failed = false;

try {
    $guard->assertPositiveAmount(0);
} catch (\InvalidArgumentException $e) {
    $failed = true;
}

if (!$failed) {
    echo "FAIL: zero amount must be rejected.\n";
    exit(1);
}

echo "PASS: zero amount rejected.\n";

$failed = false;

try {
    $guard->assertOrderTotal(100, 101);
} catch (\InvalidArgumentException $e) {
    $failed = true;
}

if (!$failed) {
    echo "FAIL: wallet amount above order total accepted.\n";
    exit(1);
}

echo "PASS: wallet amount above order total rejected.\n";

require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_customer_order_guard.php';

$orderGuard = new \OhbonoWalletCustomerOrderGuard();

$orderGuard->assertOwnership([
    'order_id' => 10,
    'customer_id' => 20
], 20);

echo "PASS: valid customer/order ownership.\n";

$failed = false;

try {
    $orderGuard->assertOwnership([
        'order_id' => 10,
        'customer_id' => 21
    ], 20);
} catch (\RuntimeException $e) {
    $failed = true;
}

if (!$failed) {
    echo "FAIL: cross-customer order accepted.\n";
    exit(1);
}

echo "PASS: cross-customer order rejected.\n";

echo "All OHBONO Wallet 0124 production guard tests passed.\n";
