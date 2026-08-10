<?php
/**
 * Dependency-free installation checks.
 */

$root = dirname(__DIR__);

$checks = [
    [$root . '/install.php', 'CREATE TABLE IF NOT EXISTS'],
    [$root . '/install.php', 'wallet_payment_state'],
    [$root . '/install.php', 'wallet_admin_audit'],
    [$root . '/uninstall.php', 'Financial data was preserved'],
    [$root . '/extension.json', '"compatibility": "OpenCart 4.1.x"'],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_permissions.php',
        'addPermission'
    ],
    [
        $root . '/upload/admin/controller/extension/ohbono/module/wallet_menu.php',
        'Wallet Reconciliation'
    ]
];

$failed = 0;

foreach ($checks as [$file, $needle]) {
    if (!is_file($file)) {
        echo "FAIL: Missing {$file}\n";
        $failed++;
        continue;
    }

    $contents = file_get_contents($file);

    if (strpos($contents, $needle) === false) {
        echo "FAIL: {$needle} not found in {$file}\n";
        $failed++;
        continue;
    }

    echo "PASS: {$file} contains {$needle}\n";
}

if ($failed > 0) {
    exit(1);
}

echo "All OHBONO Wallet 0106–0108 installation checks passed.\n";
