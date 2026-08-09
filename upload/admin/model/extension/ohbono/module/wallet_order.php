<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletOrder extends \Opencart\System\Engine\Model
{
    public function getOrderWalletTransactions(int $order_id): array
    {
        if ($order_id <= 0) {
            return [];
        }

        return $this->db->query(
            "SELECT transaction_id,
                    customer_id,
                    type,
                    direction,
                    amount,
                    balance_before,
                    balance_after,
                    reference,
                    order_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE order_id = '" . (int)$order_id . "'
             ORDER BY transaction_id ASC"
        )->rows;
    }
}
