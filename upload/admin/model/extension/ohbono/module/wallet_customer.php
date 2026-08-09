<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletCustomer extends \Opencart\System\Engine\Model
{
    public function getWallets(array $data = []): array
    {
        $sql = "SELECT w.*, c.firstname, c.lastname, c.email
                FROM `" . DB_PREFIX . "wallet` w
                LEFT JOIN `" . DB_PREFIX . "customer` c ON (c.customer_id = w.customer_id)
                WHERE 1";

        if (!empty($data['filter_customer'])) {
            $filter = $this->db->escape($data['filter_customer']);

            $sql .= " AND (
                CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $filter . "%'
                OR c.email LIKE '%" . $filter . "%'
                OR c.customer_id = '" . (int)$data['filter_customer'] . "'
            )";
        }

        if (isset($data['filter_status']) && (int)$data['filter_status'] !== -1) {
            $sql .= " AND w.status = '" . (int)$data['filter_status'] . "'";
        }

        $sql .= " ORDER BY w.wallet_id DESC";

        $start = max(0, (int)($data['start'] ?? 0));
        $limit = max(1, min(100, (int)($data['limit'] ?? 20)));

        $sql .= " LIMIT " . $start . ", " . $limit;

        return $this->db->query($sql)->rows;
    }

    public function getTotalWallets(array $data = []): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM `" . DB_PREFIX . "wallet` w
                LEFT JOIN `" . DB_PREFIX . "customer` c ON (c.customer_id = w.customer_id)
                WHERE 1";

        if (!empty($data['filter_customer'])) {
            $filter = $this->db->escape($data['filter_customer']);

            $sql .= " AND (
                CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $filter . "%'
                OR c.email LIKE '%" . $filter . "%'
                OR c.customer_id = '" . (int)$data['filter_customer'] . "'
            )";
        }

        if (isset($data['filter_status']) && (int)$data['filter_status'] !== -1) {
            $sql .= " AND w.status = '" . (int)$data['filter_status'] . "'";
        }

        return (int)$this->db->query($sql)->row['total'];
    }

    public function getCustomer(int $customer_id): array
    {
        $query = $this->db->query(
            "SELECT `customer_id`, `firstname`, `lastname`, `email`
             FROM `" . DB_PREFIX . "customer`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getWallet(int $customer_id): array
    {
        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getTransactions(int $customer_id, int $start = 0, int $limit = 50): array
    {
        $start = max(0, $start);
        $limit = max(1, min(100, $limit));

        return $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             ORDER BY `transaction_id` DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }
}
