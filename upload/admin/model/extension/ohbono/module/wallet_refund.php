<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletRefund extends \Opencart\System\Engine\Model
{
    public function getRefunds(array $filters = [], int $start = 0, int $limit = 50): array
    {
        $sql = "SELECT wo.wallet_order_id,
                       wo.order_id,
                       wo.customer_id,
                       wo.amount,
                       wo.transaction_id,
                       wo.reference,
                       wo.date_added,
                       c.firstname,
                       c.lastname,
                       c.email
                FROM `" . DB_PREFIX . "wallet_order` wo
                LEFT JOIN `" . DB_PREFIX . "customer` c
                    ON c.customer_id = wo.customer_id
                WHERE wo.status = '2'";

        if (!empty($filters['order_id'])) {
            $sql .= " AND wo.order_id = '" . (int)$filters['order_id'] . "'";
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND wo.customer_id = '" . (int)$filters['customer_id'] . "'";
        }

        if (!empty($filters['date_start'])) {
            $sql .= " AND wo.date_added >= '" .
                $this->db->escape($filters['date_start']) . " 00:00:00'";
        }

        if (!empty($filters['date_end'])) {
            $sql .= " AND wo.date_added <= '" .
                $this->db->escape($filters['date_end']) . " 23:59:59'";
        }

        $sql .= " ORDER BY wo.wallet_order_id DESC";
        $sql .= " LIMIT " . max(0, $start) . ", " .
            max(1, min(100, $limit));

        return $this->db->query($sql)->rows;
    }

    public function getTotalRefunds(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM `" . DB_PREFIX . "wallet_order` wo
                WHERE wo.status = '2'";

        if (!empty($filters['order_id'])) {
            $sql .= " AND wo.order_id = '" . (int)$filters['order_id'] . "'";
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND wo.customer_id = '" . (int)$filters['customer_id'] . "'";
        }

        if (!empty($filters['date_start'])) {
            $sql .= " AND wo.date_added >= '" .
                $this->db->escape($filters['date_start']) . " 00:00:00'";
        }

        if (!empty($filters['date_end'])) {
            $sql .= " AND wo.date_added <= '" .
                $this->db->escape($filters['date_end']) . " 23:59:59'";
        }

        return (int)$this->db->query($sql)->row['total'];
    }
}
