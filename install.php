<?php
/**
 * OHBONO Wallet installer / upgrader — Commit 0034 additions.
 *
 * Add this method to the existing installer and invoke it after the wallet
 * transaction tables have been created.
 */

class ControllerExtensionOhbonoInstall extends Controller
{
    public function audit0034(): void
    {
        $this->createAuditTable();

        $this->response->setOutput(
            'OHBONO Wallet audit migration 0034 completed.'
        );
    }

    private function createAuditTable(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wallet_audit` (
                `audit_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `customer_id` INT(11) NOT NULL,
                `transaction_id` BIGINT(20) NOT NULL DEFAULT '0',
                `admin_user_id` INT(11) NOT NULL DEFAULT '0',
                `action` VARCHAR(64) NOT NULL,
                `amount` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
                `balance_before` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
                `balance_after` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
                `reference` VARCHAR(128) NOT NULL DEFAULT '',
                `reason` VARCHAR(500) NOT NULL,
                `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
                `user_agent` VARCHAR(500) NOT NULL DEFAULT '',
                `date_added` DATETIME NOT NULL,
                PRIMARY KEY (`audit_id`),
                KEY `idx_wallet_audit_customer` (`customer_id`, `audit_id`),
                KEY `idx_wallet_audit_admin` (`admin_user_id`, `audit_id`),
                KEY `idx_wallet_audit_transaction` (`transaction_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }
}
