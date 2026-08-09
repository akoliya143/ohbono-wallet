<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Account;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getBalance(int $customer_id): float
    {
        if ($customer_id <= 0) {
            return 0.0;
        }

        $query = $this->db->query(
            "SELECT balance
             FROM `" . DB_PREFIX . "wallet`
             WHERE customer_id = '" . (int)$customer_id . "'
             AND status = '1'
             LIMIT 1"
        );

        return $query->num_rows
            ? round((float)$query->row['balance'], 4)
            : 0.0;
    }

    public function getTransactions(
        int $customer_id,
        int $start = 0,
        int $limit = 20
    ): array {
        if ($customer_id <= 0) {
            return [];
        }

        $start = max(0, $start);
        $limit = max(1, min(100, $limit));

        $query = $this->db->query(
            "SELECT transaction_id,
                    type,
                    direction,
                    amount,
                    balance_before,
                    balance_after,
                    reference,
                    order_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'
             ORDER BY transaction_id DESC
             LIMIT " . $start . ", " . $limit
        );

        return $query->rows;
    }

    public function getTransaction(
        int $customer_id,
        int $transaction_id
    ): array {
        if ($customer_id <= 0 || $transaction_id <= 0) {
            return [];
        }

        $query = $this->db->query(
            "SELECT transaction_id,
                    type,
                    direction,
                    amount,
                    balance_before,
                    balance_after,
                    reference,
                    comment,
                    order_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'
             AND transaction_id = '" . (int)$transaction_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getTotalTransactions(int $customer_id): int
    {
        if ($customer_id <= 0) {
            return 0;
        }

        $query = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'"
        );

        return (int)$query->row['total'];
    }

    public function getSummary(int $customer_id): array
    {
        $summary = [
            'credited' => 0.0,
            'debited' => 0.0,
            'count' => 0
        ];

        if ($customer_id <= 0) {
            return $summary;
        }

        $query = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END), 0) AS credited,
                COALESCE(SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END), 0) AS debited,
                COUNT(*) AS transaction_count
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'"
        );

        if ($query->num_rows) {
            $summary['credited'] = round((float)$query->row['credited'], 4);
            $summary['debited'] = round((float)$query->row['debited'], 4);
            $summary['count'] = (int)$query->row['transaction_count'];
        }

        return $summary;
    }
}
