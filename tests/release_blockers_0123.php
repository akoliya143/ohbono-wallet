<?php
/**
 * Release blocker evaluator.
 */

function releaseBlocked(array $results): array {
    $blocking = [];

    foreach ($results as $scenario => $result) {
        if ($result !== 'pass') {
            $blocking[] = $scenario;
        }
    }

    return $blocking;
}

$example = releaseBlocked([
    'wallet_only' => 'pass',
    'partial_wallet' => 'pass',
    'duplicate_callback' => 'fail'
]);

if ($example !== ['duplicate_callback']) {
    echo "FAIL: release blocker evaluation failed.\n";
    exit(1);
}

echo "PASS: failed staging scenario blocks release.\n";
echo "All OHBONO Wallet 0123 release blocker checks passed.\n";
