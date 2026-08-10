<?php
/**
 * OHBONO Wallet installer
 *
 * OpenCart 4.x extension installer helper.
 *
 * This script is intentionally idempotent: running it more than once should
 * not duplicate tables, columns or permission records.
 */

if (!defined('DIR_SYSTEM')) {
    require_once 'config.php';
}

require_once DIR_SYSTEM . 'library/db/mysqli.php';

$db = new \Opencart\System\Library\DB\MySQLi(
    DB_HOSTNAME,
    DB_USERNAME,
    DB_PASSWORD,
    DB_DATABASE,
    DB_PORT
);

$prefix = DB_PREFIX;

$queries = [
    "CREATE TABLE IF NOT EXISTS `" . $prefix . "wallet_payment_state` (
        `wallet_payment_state_id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `customer_id` int(11) NOT NULL,
        `state` varchar(40) NOT NULL,
        `wallet_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
        `remaining_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
        `date_added` datetime NOT NULL,
        `date_modified` datetime NOT NULL,
        PRIMARY KEY (`wallet_payment_state_id`),
        UNIQUE KEY `order_id` (`order_id`),
        KEY `customer_id` (`customer_id`),
        KEY `state` (`state`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    "CREATE TABLE IF NOT EXISTS `" . $prefix . "wallet_admin_audit` (
        `wallet_admin_audit_id` int(11) NOT NULL AUTO_INCREMENT,
        `admin_user_id` int(11) NOT NULL DEFAULT '0',
        `customer_id` int(11) NOT NULL DEFAULT '0',
        `transaction_id` int(11) NOT NULL DEFAULT '0',
        `action` varchar(80) NOT NULL,
        `reason` varchar(500) NOT NULL,
        `date_added` datetime NOT NULL,
        PRIMARY KEY (`wallet_admin_audit_id`),
        KEY `admin_user_id` (`admin_user_id`),
        KEY `customer_id` (`customer_id`),
        KEY `transaction_id` (`transaction_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
];

foreach ($queries as $query) {
    $db->query($query);
}

echo 'OHBONO Wallet installation completed.' . PHP_EOL;
