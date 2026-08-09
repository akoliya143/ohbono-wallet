<?php
/**
 * OHBONO Wallet Batch 0052-0054 migration.
 *
 * Merge these methods into the existing installer.
 */
class ControllerExtensionOhbonoInstall extends Controller
{
    public function audit0052(): void
    {
        $this->ensureTransactionIndexes();

        $this->response->setOutput(
            'OHBONO Wallet audit migration 0052 completed.'
        );
    }

    public function audit0053(): void
    {
        $this->ensureTransactionIndexes();

        $this->response->setOutput(
            'OHBONO Wallet audit migration 0053 completed.'
        );
    }

    public function audit0054(): void
    {
        $this->ensureTransactionIndexes();

        $this->response->setOutput(
            'OHBONO Wallet audit migration 0054 completed.'
        );
    }

    private function ensureTransactionIndexes(): void
    {
        $indexes = [
            'idx_wallet_customer_date' => '`customer_id`, `date_added`',
            'idx_wallet_order_date' => '`order_id`, `date_added`',
            'idx_wallet_type_date' => '`type`, `date_added`'
        ];

        foreach ($indexes as $name => $columns) {
            $query = $this->db->query(
                "SHOW INDEX FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE Key_name = '" . $this->db->escape($name) . "'"
            );

            if (!$query->num_rows) {
                $this->db->query(
                    "ALTER TABLE `" . DB_PREFIX . "wallet_transaction`
                     ADD KEY `" . $this->db->escape($name) .
                     "` (" . $columns . ")"
                );
            }
        }
    }
}
