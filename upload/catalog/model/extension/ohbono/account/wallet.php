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
            "SELECT `balance`
             FROM `" . DB_PREFIX . "wallet`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             AND `status` = '1'
             LIMIT 1"
        );

        return $query->num_rows ? round((float)$query->row['balance'], 4) : 0.0;
    }

    public function getTransactions(
        int $customer_id,
        int $start = 0,
        int $limit = 20
    ): array {
        $start = max(0, $start);
        $limit = max(1, min(100, $limit));

        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             ORDER BY `transaction_id` DESC
             LIMIT " . $start . ", " . $limit
        );

        return $query->rows;
    }

    public function getTotalTransactions(int $customer_id): int
    {
        $query = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE `customer_id` = '" . (int)$customer_id . "'"
        );

        return (int)$query->row['total'];
    }
}
