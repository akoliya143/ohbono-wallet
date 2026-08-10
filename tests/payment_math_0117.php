<?php
/**
 * Framework-independent payment math checks.
 */

function walletSplit(
    float $total,
    float $wallet
): array {
    $total = round(max(0, $total), 4);
    $wallet = round(
        min(max(0, $wallet), $total),
        4
    );

    return [
        'wallet' => $wallet,
        'remaining' => round($total - $wallet, 4)
    ];
}

$cases = [
    [500, 500, 500, 0],
    [1000, 400, 400, 600],
    [1000, 0, 0, 1000],
    [1000, 1200, 1000, 0]
];

foreach ($cases as $case) {
    $result = walletSplit(
        $case[0],
        $case[1]
    );

    if ($result['wallet'] !== $case[2] ||
        $result['remaining'] !== $case[3]) {
        echo "FAIL: payment split mismatch\n";
        exit(1);
    }

    echo "PASS: total {$case[0]}, wallet {$case[1]}\n";
}

echo "All OHBONO Wallet 0117 payment math checks passed.\n";
