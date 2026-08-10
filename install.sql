CREATE TABLE IF NOT EXISTS `oc_wallet_staging_evidence` (
  `evidence_id` int(11) NOT NULL AUTO_INCREMENT,
  `scenario` varchar(120) NOT NULL,
  `result` varchar(20) NOT NULL,
  `reference` varchar(160) NOT NULL,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `notes` varchar(2000) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`evidence_id`),
  KEY `scenario` (`scenario`),
  KEY `result` (`result`),
  KEY `order_id` (`order_id`),
  KEY `date_added` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
