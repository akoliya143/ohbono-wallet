<?php
/**
 * OHBONO Wallet Pro - single installer
 *
 * OpenCart 4.1.x
 *
 * Run from the OpenCart root:
 *   php install.php
 *
 * The installer:
 *  - creates/updates wallet tables
 *  - creates default wallet settings
 *  - registers required events
 *  - is safe to run more than once
 *
 * IMPORTANT:
 * This installer expects the extension files from the upload/ directory
 * to already be copied into the OpenCart installation.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "OHBONO Wallet Pro installer\n";
echo "===========================\n";

$config_file = __DIR__ . '/config.php';

if (!is_file($config_file)) {
    exit("ERROR: OpenCart config.php was not found.\n");
}

require_once $config_file;

if (!defined('DB_HOSTNAME')) {
    exit("ERROR: Database configuration is unavailable.\n");
}

try {
    $mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

    if ($mysqli->connect_errno) {
        throw new RuntimeException($mysqli->connect_error);
    }

    $mysqli->set_charset('utf8mb4');

    $prefix = DB_PREFIX;

    $queries = [];

    $queries[] = "
        CREATE TABLE IF NOT EXISTS `{$prefix}wallet` (
            `wallet_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `customer_id` INT UNSIGNED NOT NULL,
            `balance` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`wallet_id`),
            UNIQUE KEY `uk_wallet_customer` (`customer_id`),
            KEY `idx_wallet_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    $queries[] = "
        CREATE TABLE IF NOT EXISTS `{$prefix}wallet_transaction` (
            `transaction_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `wallet_id` INT UNSIGNED NOT NULL,
            `customer_id` INT UNSIGNED NOT NULL,
            `type` VARCHAR(32) NOT NULL,
            `direction` ENUM('credit','debit') NOT NULL,
            `amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `balance_before` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `balance_after` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `reference` VARCHAR(128) NOT NULL DEFAULT '',
            `comment` VARCHAR(255) NOT NULL DEFAULT '',
            `order_id` INT UNSIGNED NOT NULL DEFAULT 0,
            `admin_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
            `date_added` DATETIME NOT NULL,
            PRIMARY KEY (`transaction_id`),
            KEY `idx_wallet_transaction_customer` (`customer_id`),
            KEY `idx_wallet_transaction_wallet` (`wallet_id`),
            KEY `idx_wallet_transaction_order` (`order_id`),
            KEY `idx_wallet_transaction_type` (`type`),
            KEY `idx_wallet_transaction_date` (`date_added`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    $queries[] = "
        CREATE TABLE IF NOT EXISTS `{$prefix}wallet_order` (
            `wallet_order_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `order_id` INT UNSIGNED NOT NULL,
            `customer_id` INT UNSIGNED NOT NULL,
            `amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `transaction_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 2=refunded',
            `date_added` DATETIME NOT NULL,
            PRIMARY KEY (`wallet_order_id`),
            UNIQUE KEY `uk_wallet_order_order` (`order_id`),
            KEY `idx_wallet_order_customer` (`customer_id`),
            KEY `idx_wallet_order_transaction` (`transaction_id`),
            KEY `idx_wallet_order_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    foreach ($queries as $query) {
        if (!$mysqli->query($query)) {
            throw new RuntimeException($mysqli->error);
        }
    }

    echo "Database tables: OK\n";

    $settings = [
        'ohbono_wallet_status' => '1',
        'ohbono_wallet_allow_checkout' => '1',
        'ohbono_wallet_allow_negative' => '0',
        'ohbono_wallet_minimum_use' => '0',
        'ohbono_wallet_maximum_use' => '0',
        'ohbono_wallet_sort_order' => '5',
        'ohbono_wallet_history_limit' => '20',
        'payment_wallet_status' => '1',
        'payment_wallet_sort_order' => '1'
    ];

    $setting_table = $prefix . 'setting';

    foreach ($settings as $key => $value) {
        $key_escaped = $mysqli->real_escape_string($key);
        $value_escaped = $mysqli->real_escape_string($value);

        $exists = $mysqli->query(
            "SELECT `setting_id`
             FROM `{$setting_table}`
             WHERE `code` = 'ohbono_wallet'
             AND `key` = '{$key_escaped}'
             LIMIT 1"
        );

        if ($exists && $exists->num_rows) {
            $mysqli->query(
                "UPDATE `{$setting_table}`
                 SET `value` = '{$value_escaped}',
                     `serialized` = '0'
                 WHERE `code` = 'ohbono_wallet'
                 AND `key` = '{$key_escaped}'"
            );
        } else {
            $mysqli->query(
                "INSERT INTO `{$setting_table}`
                 SET `store_id` = '0',
                     `code` = 'ohbono_wallet',
                     `key` = '{$key_escaped}',
                     `value` = '{$value_escaped}',
                     `serialized` = '0'"
            );
        }
    }

    echo "Default settings: OK\n";

    /*
     * Migrate settings produced by earlier development batches.
     */
    $legacy = [
        'total_wallet_status' => 'ohbono_wallet_status',
        'total_wallet_allow_checkout' => 'ohbono_wallet_allow_checkout',
        'total_wallet_minimum_use' => 'ohbono_wallet_minimum_use',
        'total_wallet_maximum_use' => 'ohbono_wallet_maximum_use',
        'total_wallet_sort_order' => 'ohbono_wallet_sort_order'
    ];

    foreach ($legacy as $old => $new) {
        $old_escaped = $mysqli->real_escape_string($old);
        $new_escaped = $mysqli->real_escape_string($new);

        $legacy_result = $mysqli->query(
            "SELECT `value`, `serialized`
             FROM `{$setting_table}`
             WHERE `key` = '{$old_escaped}'
             ORDER BY `setting_id` DESC
             LIMIT 1"
        );

        if ($legacy_result && $legacy_result->num_rows) {
            $row = $legacy_result->fetch_assoc();

            $new_check = $mysqli->query(
                "SELECT `setting_id`
                 FROM `{$setting_table}`
                 WHERE `code` = 'ohbono_wallet'
                 AND `key` = '{$new_escaped}'
                 LIMIT 1"
            );

            if (!$new_check || !$new_check->num_rows) {
                $value = $mysqli->real_escape_string($row['value']);
                $serialized = (int)$row['serialized'];

                $mysqli->query(
                    "INSERT INTO `{$setting_table}`
                     SET `store_id` = '0',
                         `code` = 'ohbono_wallet',
                         `key` = '{$new_escaped}',
                         `value` = '{$value}',
                         `serialized` = '{$serialized}'"
                );
            }
        }
    }

    echo "Legacy setting migration: OK\n";

    /*
     * Register the order-created event. The exact event route is kept in one
     * place so future OpenCart compatibility changes are easy to maintain.
     */
    $event_table = $prefix . 'event';

    $code = 'ohbono_wallet_order_created';
    $trigger = 'catalog/model/checkout/order/addOrder/after';
    $action = 'extension/ohbono/payment/wallet.orderCreated';

    $code_escaped = $mysqli->real_escape_string($code);
    $trigger_escaped = $mysqli->real_escape_string($trigger);
    $action_escaped = $mysqli->real_escape_string($action);

    $event = $mysqli->query(
        "SELECT `event_id`
         FROM `{$event_table}`
         WHERE `code` = '{$code_escaped}'
         LIMIT 1"
    );

    if ($event && $event->num_rows) {
        $event_row = $event->fetch_assoc();

        $mysqli->query(
            "UPDATE `{$event_table}`
             SET `description` = 'OHBONO Wallet order-created debit',
                 `trigger` = '{$trigger_escaped}',
                 `action` = '{$action_escaped}',
                 `status` = '1',
                 `sort_order` = '1'
             WHERE `event_id` = '" . (int)$event_row['event_id'] . "'"
        );
    } else {
        $mysqli->query(
            "INSERT INTO `{$event_table}`
             SET `code` = '{$code_escaped}',
                 `description` = 'OHBONO Wallet order-created debit',
                 `trigger` = '{$trigger_escaped}',
                 `action` = '{$action_escaped}',
                 `status` = '1',
                 `sort_order` = '1',
                 `date_added` = NOW()"
        );
    }

    echo "Order event: OK\n";
    echo "\nInstallation completed.\n";
    echo "Remove install.php from the public web root after installation.\n";

    $mysqli->close();
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
