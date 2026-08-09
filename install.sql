-- OHBONO Wallet Pro - database installer
-- Replace {{DB_PREFIX}} with your OpenCart DB prefix if running manually.

CREATE TABLE IF NOT EXISTS `{{DB_PREFIX}}wallet` (
    `wallet_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` INT UNSIGNED NOT NULL,
    `balance` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`wallet_id`),
    UNIQUE KEY `uk_wallet_customer` (`customer_id`),
    KEY `idx_wallet_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `{{DB_PREFIX}}wallet_transaction` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `{{DB_PREFIX}}wallet_order` (
    `wallet_order_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    `transaction_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`wallet_order_id`),
    UNIQUE KEY `uk_wallet_order_order` (`order_id`),
    KEY `idx_wallet_order_customer` (`customer_id`),
    KEY `idx_wallet_order_transaction` (`transaction_id`),
    KEY `idx_wallet_order_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
