CREATE TABLE IF NOT EXISTS `{{DB_PREFIX}}wallet_order` (
    `wallet_order_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    `transaction_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`wallet_order_id`),
    UNIQUE KEY `uk_wallet_order_order` (`order_id`),
    KEY `idx_wallet_order_customer` (`customer_id`),
    KEY `idx_wallet_order_transaction` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
