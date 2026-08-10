<?php
/**
 * Framework-independent end-to-end checkout smoke-plan assertions.
 *
 * This does not connect to a live OpenCart installation. It verifies the
 * required payment lifecycle decisions as pure data.
 */

function checkoutState(
    float $order_total,
    float $wallet_amount,
    bool $secondary_success
): string {
    $order_total = round(max(0, $order_total), 4);
    $wallet_amount = round(max(0, $wallet_amount), 4);

    if ($order_total <= 0) {
        return 'invalid';
    }

    if ($wallet_amount > $order_total) {
        return 'invalid';
    }

    $remaining = round(
        $order_total - $wallet_amount,
        4
    );

    if ($remaining <= 0.0001) {
        return 'paid_wallet';
    }

    if ($secondary_success) {
        return 'paid_wallet_and_secondary';
    }

    if ($wallet_amount > 0) {
        return 'reconciliation_required';
    }

    return 'awaiting_payment';
}

$cases = [
    [
        100.00,
        100.00,
        false,
        'paid_wallet'
    ],
    [
        100.00,
        40.00,
        true,
        'paid_wallet_and_secondary'
    ],
    [
        100.00,
        40.00,
        false,
        'reconciliation_required'
    ],
    [
        100.00,
        0.00,
        false,
        'awaiting_payment'
    ],
    [
        100.00,
        120.00,
        false,
        'invalid'
    ]
];

foreach ($cases as $case) {
    $actual = checkoutState(
        $case[0],
        $case[1],
        $case[2]
    );

    if ($actual !== $case[3]) {
        echo "FAIL: expected {$case[3]}, got {$actual}\n";
        exit(1);
    }

    echo "PASS: {$case[3]}\n";
}

echo "All OHBONO Wallet checkout smoke checks passed.\n";
