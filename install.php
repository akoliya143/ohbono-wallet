<?php
/**
 * OHBONO Wallet Batch 0070–0072 migration.
 *
 * Merge these methods into the existing extension installer.
 */
class ControllerExtensionOhbonoInstall extends Controller
{
    public function emailQueue0070(): void
    {
        $this->createEmailQueueTable();

        $this->response->setOutput(
            'OHBONO Wallet email queue migration 0070 completed.'
        );
    }

    public function emailQueue0071(): void
    {
        $this->createEmailQueueTable();

        $this->response->setOutput(
            'OHBONO Wallet email queue migration 0071 completed.'
        );
    }

    public function emailQueue0072(): void
    {
        $this->createEmailQueueTable();

        $this->response->setOutput(
            'OHBONO Wallet email queue migration 0072 completed.'
        );
    }

    private function createEmailQueueTable(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" .
            DB_PREFIX . "wallet_email_queue` (
                `queue_id` INT(11) NOT NULL AUTO_INCREMENT,
                `customer_id` INT(11) NOT NULL,
                `transaction_id` INT(11) NOT NULL,
                `notification_type` VARCHAR(64) NOT NULL,
                `subject` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
                `attempts` INT(11) NOT NULL DEFAULT '0',
                `available_at` DATETIME NOT NULL,
                `date_added` DATETIME NOT NULL,
                `date_started` DATETIME NULL DEFAULT NULL,
                `date_sent` DATETIME NULL DEFAULT NULL,
                `last_error` TEXT NULL,
                PRIMARY KEY (`queue_id`),
                UNIQUE KEY `uniq_wallet_email_transaction_type`
                    (`transaction_id`, `notification_type`),
                KEY `idx_wallet_email_status_available`
                    (`status`, `available_at`),
                KEY `idx_wallet_email_customer`
                    (`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }
}
