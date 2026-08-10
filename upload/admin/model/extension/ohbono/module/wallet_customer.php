<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletCustomer extends \Opencart\System\Engine\Model
{
    public function getCustomers(
        string $name = '',
        string $email = '',
        int $start = 0,
        int $limit = 100
    ): array {
        $start = max(0, $start);
        $limit = max(1, min(200, $limit));

        $where = [];

        if ($name !== '') {
            $name = $this->db->escape($name);
            $where[] = "(CONCAT(c.firstname, ' ', c.lastname) LIKE '%" .
                $name . "%')";
        }

        if ($email !== '') {
            $where[] = "c.email LIKE '%" .
                $this->db->escape($email) . "%'";
        }

        $condition = $where
            ? ' WHERE ' . implode(' AND ', $where)
            : '';

        return $this->db->query(
            "SELECT c.customer_id,
                    c.firstname,
                    c.lastname,
                    c.email,
                    c.status AS customer_status,
                    w.wallet_id,
                    w.balance,
                    w.status AS wallet_status,
                    w.date_modified
             FROM `" . DB_PREFIX . "customer` c
             LEFT JOIN `" . DB_PREFIX . "wallet` w
                ON w.customer_id = c.customer_id
             " . $condition . "
             ORDER BY c.customer_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }

    public function getCustomerWallet(
        int $customer_id
    ): array {
        if ($customer_id <= 0) {
            return [];
        }

        $query = $this->db->query(
            "SELECT c.customer_id,
                    c.firstname,
                    c.lastname,
                    c.email,
                    c.status AS customer_status,
                    w.wallet_id,
                    w.balance,
                    w.status AS wallet_status,
                    w.date_added,
                    w.date_modified
             FROM `" . DB_PREFIX . "customer` c
             LEFT JOIN `" . DB_PREFIX . "wallet` w
                ON w.customer_id = c.customer_id
             WHERE c.customer_id = '" .
                (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }
}
