<?php
/**
 * OHBONO Wallet Schema Validator
 *
 * Verifies the minimum database structures required by the wallet extension.
 * Intended for installation/diagnostics, not as a substitute for migrations.
 */
class OhbonoWalletSchemaValidator
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function validate(): array
    {
        $tables = [
            DB_PREFIX . 'wallet',
            DB_PREFIX . 'wallet_transaction',
            DB_PREFIX . 'wallet_admin_audit',
            DB_PREFIX . 'wallet_payment_state'
        ];

        $missing = [];

        foreach ($tables as $table) {
            $result = $this->db->query(
                "SHOW TABLES LIKE '" .
                $this->db->escape($table) . "'"
            );

            if (!$result->num_rows) {
                $missing[] = $table;
            }
        }

        return [
            'valid' => !$missing,
            'missing_tables' => $missing
        ];
    }
}
