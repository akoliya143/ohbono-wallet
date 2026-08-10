<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

/**
 * Read-only environment diagnostics.
 *
 * This model intentionally does not register events or mutate financial data.
 */
class WalletEnvironment extends \Opencart\System\Engine\Model
{
    public function getVersion(): string
    {
        return defined('VERSION') ? VERSION : '';
    }

    public function getTables(): array
    {
        $prefix = DB_PREFIX;

        $required = [
            $prefix . 'wallet',
            $prefix . 'wallet_transaction',
            $prefix . 'wallet_admin_audit',
            $prefix . 'wallet_payment_state'
        ];

        $result = [];

        foreach ($required as $table) {
            $query = $this->db->query(
                "SHOW TABLES LIKE '" .
                $this->db->escape($table) . "'"
            );

            $result[$table] = (bool)$query->num_rows;
        }

        return $result;
    }

    public function getEventTableExists(): bool
    {
        $query = $this->db->query(
            "SHOW TABLES LIKE '" .
            $this->db->escape(DB_PREFIX . 'event') . "'"
        );

        return (bool)$query->num_rows;
    }
}
