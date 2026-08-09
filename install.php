<?php
/**
 * OHBONO Wallet Commit 0036 migration.
 *
 * Add this migration method to the existing installer and invoke it during
 * installation/upgrade after the wallet_order table exists.
 */
class ControllerExtensionOhbonoInstall extends Controller
{
    public function refund0036(): void
    {
        $this->ensureWalletOrderColumns();
        $this->ensureWalletOrderIndexes();

        $this->response->setOutput(
            'OHBONO Wallet refund migration 0036 completed.'
        );
    }

    private function ensureWalletOrderColumns(): void
    {
        $this->ensureColumn(
            DB_PREFIX . 'wallet_order',
            'reference',
            "VARCHAR(128) NOT NULL DEFAULT ''"
        );
    }

    private function ensureWalletOrderIndexes(): void
    {
        $query = $this->db->query(
            "SHOW INDEX FROM `" . DB_PREFIX . "wallet_order`
             WHERE Key_name = 'uk_wallet_order_ref'"
        );

        if (!$query->num_rows) {
            $this->db->query(
                "ALTER TABLE `" . DB_PREFIX . "wallet_order`
                 ADD UNIQUE KEY `uk_wallet_order_ref`
                 (`order_id`, `reference`, `status`)"
            );
        }
    }

    private function ensureColumn(
        string $table,
        string $column,
        string $definition
    ): void {
        $query = $this->db->query(
            "SHOW COLUMNS FROM `" . $this->db->escape($table) . "`
             LIKE '" . $this->db->escape($column) . "'"
        );

        if (!$query->num_rows) {
            $this->db->query(
                "ALTER TABLE `" . $this->db->escape($table) . "`
                 ADD COLUMN `" . $this->db->escape($column) . "` " . $definition
            );
        }
    }
}
