<?php
require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_order_state_guard.php';

$guard = new \OhbonoWalletOrderStateGuard();

$guard->assertCapturable(
    ['order_status_id' => 2],
    [1, 2, 3]
);

echo "PASS: capturable order state accepted.\n";

$failed = false;

try {
    $guard->assertCapturable(
        ['order_status_id' => 9],
        [1, 2, 3]
    );
} catch (\RuntimeException $e) {
    $failed = true;
}

if (!$failed) {
    echo "FAIL: invalid capture state accepted.\n";
    exit(1);
}

echo "PASS: invalid capture state rejected.\n";

$guard->assertRefundable(
    ['order_status_id' => 3],
    [2, 3, 4]
);

echo "PASS: refundable order state accepted.\n";

echo "All OHBONO Wallet 0128 order-state checks passed.\n";
