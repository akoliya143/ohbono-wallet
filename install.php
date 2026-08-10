<?php
/**
 * OHBONO Wallet Batch 0076–0078 migration.
 *
 * Merge these methods into the existing extension installer rather than
 * replacing an existing installer class.
 */
class ControllerExtensionOhbonoInstall extends Controller
{
    public function adminWallet0076(): void
    {
        $this->createAuditTable();

        $this->response->setOutput(
            'OHBONO Wallet admin audit migration 0076 completed.'
        );
    }

    public function adminWallet0077(): void
    {
        $this->createAuditTable();

        $this->response->setOutput(
            'OHBONO Wallet admin transaction migration 0077 completed.'
        );
    }

    public function adminWallet0078(): void
    {
        $this->createAuditTable();

        $this->response->setOutput(
            'OHBONO Wallet admin audit migration 0078 completed.'
        );
    }

    private function createAuditTable(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" .
            DB_PREFIX . "wallet_admin_audit` (
                `audit_id` INT(11) NOT NULL AUTO_INCREMENT,
                `admin_user_id` INT(11) NOT NULL,
                `customer_id` INT(11) NOT NULL,
                `transaction_id` INT(11) NOT NULL,
                `action` VARCHAR(64) NOT NULL,
                `reason` TEXT NOT NULL,
                `date_added` DATETIME NOT NULL,
                PRIMARY KEY (`audit_id`),
                KEY `idx_wallet_audit_customer`
                    (`customer_id`, `date_added`),
                KEY `idx_wallet_audit_transaction`
                    (`transaction_id`),
                KEY `idx_wallet_audit_admin`
                    (`admin_user_id`, `date_added`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }
}
