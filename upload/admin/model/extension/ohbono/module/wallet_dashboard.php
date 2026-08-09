<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletDashboard extends \Opencart\System\Engine\Model
{
    public function getStats(): array
    {
        $balance = $this->db->query(
            "SELECT
                COALESCE(SUM(`balance`), 0) AS total_balance,
                COUNT(*) AS customer_count
             FROM `" . DB_PREFIX . "wallet`
             WHERE `status` = '1'"
        )->row;

        $credits = $this->db->query(
            "SELECT COALESCE(SUM(`amount`), 0) AS total
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE `direction` = 'credit'"
        )->row;

        $debits = $this->db->query(
            "SELECT COALESCE(SUM(`amount`), 0) AS total
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE `direction` = 'debit'"
        )->row;

        return [
            'total_balance' => round((float)$balance['total_balance'], 4),
            'customer_count' => (int)$balance['customer_count'],
            'total_credits' => round((float)$credits['total'], 4),
            'total_debits' => round((float)$debits['total'], 4)
        ];
    }

    public function getRecentTransactions(int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));

        $query = $this->db->query(
            "SELECT wt.*, c.firstname, c.lastname
             FROM `" . DB_PREFIX . "wallet_transaction` wt
             LEFT JOIN `" . DB_PREFIX . "customer` c
                ON (c.customer_id = wt.customer_id)
             ORDER BY wt.transaction_id DESC
             LIMIT " . $limit
        );

        return $query->rows;
    }
}
