<?php
/**
 * OHBONO Wallet Batch 0079–0081 migration.
 *
 * Merge these methods into the existing extension installer.
 */
class ControllerExtensionOhbonoInstall extends Controller
{
    public function adminAdjustment0079(): void
    {
        $this->createAuditTable();
        $this->response->setOutput(
            'OHBONO Wallet admin adjustment migration 0079 completed.'
        );
    }

    public function adminAdjustment0080(): void
    {
        $this->createAuditTable();
        $this->response->setOutput(
            'OHBONO Wallet admin adjustment migration 0080 completed.'
        );
    }

    public function adminAdjustment0081(): void
    {
        $this->createAuditTable();
        $this->response->setOutput(
            'OHBONO Wallet admin adjustment migration 0081 completed.'
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
