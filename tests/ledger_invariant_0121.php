<?php
/**
 * Pure ledger invariant tests.
 */

function transition(
    float $before,
    float $amount,
    string $direction
): float {
    $before = round($before, 4);
    $amount = round(abs($amount), 4);

    return $direction === 'debit'
        ? round($before - $amount, 4)
        : round($before + $amount, 4);
}

$cases = [
    [100.00, 25.00, 'debit', 75.00],
    [75.00, 25.00, 'credit', 100.00],
    [100.00, 0.00, 'credit', 100.00]
];

foreach ($cases as $case) {
    $actual = transition(
        $case[0],
        $case[1],
        $case[2]
    );

    if ($actual !== $case[3]) {
        echo "FAIL: ledger transition mismatch\n";
        exit(1);
    }

    echo "PASS: {$case[2]} {$case[1]}\n";
}

echo "All OHBONO Wallet 0121 ledger invariant checks passed.\n";
