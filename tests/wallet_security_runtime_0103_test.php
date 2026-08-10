<?php
/**
 * Runtime unit checks for the pure wallet security helper.
 *
 * Run:
 *   php tests/wallet_security_runtime_0103_test.php
 */

require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_security.php';

$assert = function ($condition, $message) {
    if (!$condition) {
        throw new RuntimeException(
            'FAIL: ' . $message
        );
    }

    echo 'PASS: ' . $message . PHP_EOL;
};

$assert(
    \OhbonoWalletSecurity::normalizeAmount('12.34567') === 12.3457,
    'amount normalization rounds to four decimals'
);

$assert(
    \OhbonoWalletSecurity::validateReference(
        'OBW-PAY-123'
    ) === 'OBW-PAY-123',
    'valid reference is accepted'
);

$rejected = false;

try {
    \OhbonoWalletSecurity::validateReference(
        'invalid reference'
    );
} catch (\InvalidArgumentException $e) {
    $rejected = true;
}

$assert(
    $rejected,
    'invalid reference characters are rejected'
);

$rejected = false;

try {
    \OhbonoWalletSecurity::validateReason(
        'x'
    );
} catch (\InvalidArgumentException $e) {
    $rejected = true;
}

$assert(
    $rejected,
    'short admin reasons are rejected'
);

echo 'All OHBONO Wallet 0103–0105 runtime checks passed.' .
    PHP_EOL;
