<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

class WalletHistory extends \Opencart\System\Engine\Model
{
    public function getTransactions(
        int $customer_id,
        int $start = 0,
        int $limit = 20
    ): array {
        if ($customer_id <= 0) {
            return [];
        }

        $start = max(0, $start);
        $limit = max(1, min(50, $limit));

        return $this->db->query(
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
             WHERE customer_id = '" .
                (int)$customer_id . "'
             ORDER BY transaction_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }

    public function getTotalTransactions(
        int $customer_id
    ): int {
        if ($customer_id <= 0) {
            return 0;
        }

        $query = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" .
                (int)$customer_id . "'"
        );

        return (int)$query->row['total'];
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
                    order_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" .
                (int)$customer_id . "'
             AND transaction_id = '" .
                (int)$transaction_id . "'
             LIMIT 1"
        );

        return $query->num_rows
            ? $query->row
            : [];
    }
}
