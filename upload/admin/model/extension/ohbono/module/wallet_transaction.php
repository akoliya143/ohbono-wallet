<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletTransaction extends \Opencart\System\Engine\Model
{
    public function getTransactions(
        int $customer_id = 0,
        int $start = 0,
        int $limit = 100
    ): array {
        $start = max(0, $start);
        $limit = max(1, min(200, $limit));

        $where = [];

        if ($customer_id > 0) {
            $where[] = "wt.customer_id = '" .
                (int)$customer_id . "'";
        }

        $condition = $where
            ? ' WHERE ' . implode(' AND ', $where)
            : '';

        return $this->db->query(
            "SELECT wt.transaction_id,
                    wt.customer_id,
                    wt.type,
                    wt.direction,
                    wt.amount,
                    wt.balance_before,
                    wt.balance_after,
                    wt.reference,
                    wt.order_id,
                    wt.admin_user_id,
                    wt.date_added,
                    CONCAT(
                        c.firstname, ' ', c.lastname
                    ) AS customer,
                    c.email
             FROM `" . DB_PREFIX . "wallet_transaction` wt
             LEFT JOIN `" . DB_PREFIX . "customer` c
                ON c.customer_id = wt.customer_id
             " . $condition . "
             ORDER BY wt.transaction_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }

    public function getOrderWalletPayment(
        int $order_id
    ): array {
        if ($order_id <= 0) {
            return [];
        }

        $query = $this->db->query(
            "SELECT wt.transaction_id,
                    wt.customer_id,
                    wt.amount,
                    wt.balance_before,
                    wt.balance_after,
                    wt.reference,
                    wt.date_added
             FROM `" . DB_PREFIX . "wallet_transaction` wt
             WHERE wt.order_id = '" .
                (int)$order_id . "'
             AND wt.type = 'wallet_payment'
             AND wt.direction = 'debit'
             ORDER BY wt.transaction_id DESC
             LIMIT 1"
        );

        return $query->num_rows
            ? $query->row
            : [];
    }
}
