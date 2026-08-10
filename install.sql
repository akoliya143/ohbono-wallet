CREATE TABLE IF NOT EXISTS `oc_wallet_payment_state` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
