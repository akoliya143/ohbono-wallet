<?php
/**
 * OHBONO Wallet Pro - uninstall helper.
 *
 * This disables/removes wallet settings and events.
 *
 * By default wallet tables are PRESERVED because they contain financial
 * history. Use the explicit --drop-data CLI flag only when you intentionally
 * want to delete wallet financial data.
 */

declare(strict_types=1);

echo "OHBONO Wallet Pro uninstaller\n";
echo "=============================\n";

if (PHP_SAPI !== 'cli') {
    exit("Run this script from CLI only.\n");
}

require_once __DIR__ . '/config.php';

$drop_data = in_array('--drop-data', $argv ?? [], true);

try {
    $mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

    if ($mysqli->connect_errno) {
        throw new RuntimeException($mysqli->connect_error);
    }

    $mysqli->set_charset('utf8mb4');

    $prefix = DB_PREFIX;

    $code = $mysqli->real_escape_string('ohbono_wallet_order_created');

    $mysqli->query(
        "DELETE FROM `{$prefix}event`
         WHERE `code` = '{$code}'"
    );

    $mysqli->query(
        "DELETE FROM `{$prefix}setting`
         WHERE `code` IN ('ohbono_wallet', 'payment_wallet')"
    );

    echo "Settings and events removed.\n";

    if ($drop_data) {
        $mysqli->query("DROP TABLE IF EXISTS `{$prefix}wallet_order`");
        $mysqli->query("DROP TABLE IF EXISTS `{$prefix}wallet_transaction`");
        $mysqli->query("DROP TABLE IF EXISTS `{$prefix}wallet`");

        echo "WARNING: wallet financial data has been deleted.\n";
    } else {
        echo "Wallet tables preserved.\n";
        echo "Use --drop-data only if permanent deletion is intended.\n";
    }

    $mysqli->close();
    echo "Uninstallation completed.\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
