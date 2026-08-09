<?php
/**
 * OHBONO Wallet Commit 0040 migration.
 *
 * This migration adds indexes required by checkout wallet idempotency and
 * order linkage.
 */
class ControllerExtensionOhbonoInstall extends Controller
{
    public function checkout0040(): void
    {
        $this->ensureTransactionIndexes();

        $this->response->setOutput(
            'OHBONO Wallet checkout migration 0040 completed.'
        );
    }

    private function ensureTransactionIndexes(): void
    {
        $this->ensureIndex(
            'wallet_transaction',
            'idx_wallet_checkout_reference',
            ['customer_id', 'type', 'reference']
        );

        $this->ensureIndex(
            'wallet_transaction',
            'idx_wallet_order',
            ['order_id', 'customer_id']
        );
    }

    private function ensureIndex(
        string $table,
        string $index,
        array $columns
    ): void {
        $query = $this->db->query(
            "SHOW INDEX FROM `" . DB_PREFIX . $this->db->escape($table) . "`
             WHERE Key_name = '" . $this->db->escape($index) . "'"
        );

        if ($query->num_rows) {
            return;
        }

        $column_sql = '`' . implode('`, `', $columns) . '`';

        $this->db->query(
            "ALTER TABLE `" . DB_PREFIX . $this->db->escape($table) . "`
             ADD KEY `" . $this->db->escape($index) . "` (" .
             $column_sql . ")"
        );
    }
}
