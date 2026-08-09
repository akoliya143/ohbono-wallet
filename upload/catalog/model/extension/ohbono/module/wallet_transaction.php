<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

class WalletTransaction extends \Opencart\System\Engine\Model
{
    public function getTransaction(
        int $customer_id,
        int $transaction_id
    ): ?array {
        if ($customer_id <= 0 || $transaction_id <= 0) {
            return null;
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
             WHERE transaction_id = '" . (int)$transaction_id . "'
             AND customer_id = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows
            ? $query->row
            : null;
    }
}
