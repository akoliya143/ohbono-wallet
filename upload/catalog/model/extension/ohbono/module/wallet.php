<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getBalance(int $customer_id): float
    {
        if ($customer_id <= 0) {
            return 0.0;
        }

        $query = $this->db->query(
            "SELECT COALESCE(balance, 0) AS balance
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
        int $limit = 5
    ): array {
        if ($customer_id <= 0) {
            return [];
        }

        $start = max(0, $start);
        $limit = max(1, min(100, $limit));

        return $this->db->query(
            "SELECT transaction_id,
                    type,
                    direction,
                    amount,
                    balance_after,
                    reference,
                    order_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'
             ORDER BY transaction_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }

    public function getRefundSummary(int $customer_id): array
    {
        $result = [
            'total' => 0.0,
            'count' => 0
        ];

        if ($customer_id <= 0) {
            return $result;
        }

        $query = $this->db->query(
            "SELECT
                COALESCE(SUM(amount), 0) AS total,
                COUNT(*) AS total_count
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'
             AND type = 'order_refund'"
        );

        if ($query->num_rows) {
            $result['total'] = round((float)$query->row['total'], 4);
            $result['count'] = (int)$query->row['total_count'];
        }

        return $result;
    }
}
