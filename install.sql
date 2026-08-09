-- OHBONO Wallet Pro
-- Database schema
-- OpenCart 4.1.0.3
--
-- Replace oc_ with the OpenCart database prefix before executing.
-- The official installer will perform this replacement automatically.

CREATE TABLE IF NOT EXISTS `oc_wallet` (
    `wallet_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` INT UNSIGNED NOT NULL,
    `balance` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`wallet_id`),
    UNIQUE KEY `uk_wallet_customer` (`customer_id`),
    KEY `idx_wallet_status` (`status`),
    KEY `idx_wallet_modified` (`date_modified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oc_wallet_transaction` (
    `transaction_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `wallet_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `type` VARCHAR(50) NOT NULL,
    `direction` ENUM('credit','debit') NOT NULL,
    `amount` DECIMAL(15,4) NOT NULL,
    `balance_before` DECIMAL(15,4) NOT NULL,
    `balance_after` DECIMAL(15,4) NOT NULL,
    `reference` VARCHAR(100) NOT NULL DEFAULT '',
    `comment` VARCHAR(1000) NOT NULL DEFAULT '',
    `created_by` INT UNSIGNED NOT NULL DEFAULT 0,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`transaction_id`),
    KEY `idx_wallet_transaction_wallet` (`wallet_id`, `transaction_id`),
    KEY `idx_wallet_transaction_customer` (`customer_id`, `transaction_id`),
    KEY `idx_wallet_transaction_order` (`order_id`),
    KEY `idx_wallet_transaction_type` (`type`),
    KEY `idx_wallet_transaction_date` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oc_wallet_order` (
    `wallet_order_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `transaction_id` BIGINT UNSIGNED NOT NULL,
    `wallet_used` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`wallet_order_id`),
    UNIQUE KEY `uk_wallet_order` (`order_id`),
    KEY `idx_wallet_order_customer` (`customer_id`),
    KEY `idx_wallet_order_transaction` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oc_wallet_setting` (
    `setting_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`setting_id`),
    UNIQUE KEY `uk_wallet_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oc_wallet_log` (
    `log_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `transaction_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `level` ENUM('info','warning','error') NOT NULL DEFAULT 'info',
    `message` VARCHAR(2000) NOT NULL,
    `context` JSON NULL,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`log_id`),
    KEY `idx_wallet_log_customer` (`customer_id`, `log_id`),
    KEY `idx_wallet_log_transaction` (`transaction_id`),
    KEY `idx_wallet_log_level` (`level`),
    KEY `idx_wallet_log_date` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `oc_wallet_setting`
    (`setting_key`, `setting_value`, `date_modified`)
VALUES
    ('status', '1', NOW()),
    ('allow_checkout', '1', NOW()),
    ('allow_partial_payment', '1', NOW()),
    ('allow_full_payment', '1', NOW()),
    ('refund_to_wallet', '1', NOW()),
    ('minimum_use', '0', NOW()),
    ('maximum_use', '0', NOW()),
    ('sort_order', '1', NOW())
ON DUPLICATE KEY UPDATE
    `setting_value` = VALUES(`setting_value`),
    `date_modified` = VALUES(`date_modified`);
