<?php
require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_regression_suite.php';

$suite = new \OhbonoWalletRegressionSuite();
$results = $suite->run();

foreach ($results as $name => $passed) {
    if (!$passed) {
        echo "FAIL: {$name}\n";
        exit(1);
    }

    echo "PASS: {$name}\n";
}

echo "All OHBONO Wallet 0127 regression checks passed.\n";
