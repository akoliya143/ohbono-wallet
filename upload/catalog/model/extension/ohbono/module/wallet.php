<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getTransactions(int $customer_id, int $start = 0, int $limit = 20): array
    {
        if ($customer_id <= 0) {
            return [];
        }

        $start = max(0, $start);
        $limit = max(1, min(100, $limit));

        return $this->db->query(
            "SELECT transaction_id, type, direction, amount,
                    balance_before, balance_after, reference,
                    order_id, date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'
             ORDER BY transaction_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }

    public function getRefundTransactions(int $customer_id, int $limit = 20): array
    {
        if ($customer_id <= 0) {
            return [];
        }

        $limit = max(1, min(100, $limit));

        return $this->db->query(
            "SELECT wt.transaction_id,
                    wt.amount,
                    wt.balance_after,
                    wt.reference,
                    wt.order_id,
                    wt.date_added,
                    wo.wallet_order_id
             FROM `" . DB_PREFIX . "wallet_transaction` wt
             LEFT JOIN `" . DB_PREFIX . "wallet_order` wo
                ON wo.transaction_id = wt.transaction_id
             WHERE wt.customer_id = '" . (int)$customer_id . "'
             AND wt.type = 'order_refund'
             ORDER BY wt.transaction_id DESC
             LIMIT " . $limit
        )->rows;
    }

    public function getOrderRefunds(int $customer_id, int $order_id): array
    {
        if ($customer_id <= 0 || $order_id <= 0) {
            return [];
        }

        return $this->db->query(
            "SELECT wt.transaction_id,
                    wt.amount,
                    wt.reference,
                    wt.date_added,
                    wo.wallet_order_id
             FROM `" . DB_PREFIX . "wallet_transaction` wt
             LEFT JOIN `" . DB_PREFIX . "wallet_order` wo
                ON wo.transaction_id = wt.transaction_id
             WHERE wt.customer_id = '" . (int)$customer_id . "'
             AND wt.order_id = '" . (int)$order_id . "'
             AND wt.type = 'order_refund'
             ORDER BY wt.transaction_id DESC"
        )->rows;
    }
}
