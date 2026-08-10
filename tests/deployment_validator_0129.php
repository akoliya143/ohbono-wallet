<?php
require_once dirname(__DIR__) .
    '/upload/system/library/ohbono/wallet_deployment_validator.php';

$validator = new \OhbonoWalletDeploymentValidator();

$result = $validator->validate(dirname(__DIR__));

if (!$result['valid']) {
    echo "FAIL: deployment files missing:\n";

    foreach ($result['missing'] as $file) {
        echo " - {$file}\n";
    }

    exit(1);
}

echo "PASS: required deployment files are present.\n";
echo "All OHBONO Wallet 0129 deployment checks passed.\n";
