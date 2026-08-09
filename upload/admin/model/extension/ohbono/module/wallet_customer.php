<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletCustomer extends \Opencart\System\Engine\Model
{
    public function getWallet(int $customer_id): ?array
    {
        if ($customer_id <= 0) {
            return null;
        }

        $query = $this->db->query(
            "SELECT w.wallet_id,
                    w.customer_id,
                    w.balance,
                    w.status,
                    w.date_added,
                    w.date_modified,
                    c.firstname,
                    c.lastname,
                    c.email
             FROM `" . DB_PREFIX . "wallet` w
             LEFT JOIN `" . DB_PREFIX . "customer` c
                ON c.customer_id = w.customer_id
             WHERE w.customer_id = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        if (!$query->num_rows) {
            return null;
        }

        return [
            'wallet_id' => (int)$query->row['wallet_id'],
            'customer_id' => (int)$query->row['customer_id'],
            'balance' => (float)$query->row['balance'],
            'status' => (int)$query->row['status'],
            'date_added' => $query->row['date_added'],
            'date_modified' => $query->row['date_modified'],
            'customer' => trim(
                $query->row['firstname'] . ' ' . $query->row['lastname']
            ),
            'email' => $query->row['email']
        ];
    }

    public function getTransactions(
        int $customer_id,
        int $start = 0,
        int $limit = 50
    ): array {
        if ($customer_id <= 0) {
            return [];
        }

        return $this->db->query(
            "SELECT transaction_id,
                    type,
                    direction,
                    amount,
                    balance_before,
                    balance_after,
                    reference,
                    order_id,
                    admin_user_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'
             ORDER BY transaction_id DESC
             LIMIT " . max(0, $start) . ", " .
             max(1, min(100, $limit))
        )->rows;
    }
}
