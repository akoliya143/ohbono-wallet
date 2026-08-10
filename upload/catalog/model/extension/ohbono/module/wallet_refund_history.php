<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

class WalletRefundHistory extends \Opencart\System\Engine\Model
{
    public function getRefunds(
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
                    amount,
                    balance_before,
                    balance_after,
                    reference,
                    order_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" .
                (int)$customer_id . "'
             AND type = 'wallet_reversal'
             AND direction = 'credit'
             ORDER BY transaction_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }

    public function getTotalRefunds(
        int $customer_id
    ): int {
        if ($customer_id <= 0) {
            return 0;
        }

        $query = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" .
                (int)$customer_id . "'
             AND type = 'wallet_reversal'
             AND direction = 'credit'"
        );

        return (int)$query->row['total'];
    }
}
