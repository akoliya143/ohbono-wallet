<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletTransactions extends \Opencart\System\Engine\Model
{
    public function getTransactions(array $filters = []): array
    {
        $where = [];

        if (!empty($filters['customer_id'])) {
            $where[] = "wt.customer_id = '" .
                (int)$filters['customer_id'] . "'";
        }

        if (!empty($filters['order_id'])) {
            $where[] = "wt.order_id = '" .
                (int)$filters['order_id'] . "'";
        }

        if (!empty($filters['type'])) {
            $where[] = "wt.type = '" .
                $this->db->escape($filters['type']) . "'";
        }

        if (!empty($filters['direction'])) {
            $where[] = "wt.direction = '" .
                $this->db->escape($filters['direction']) . "'";
        }

        $sql = "SELECT wt.transaction_id,
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
                       CONCAT(c.firstname, ' ', c.lastname) AS customer,
                       c.email,
                       CONCAT(u.firstname, ' ', u.lastname) AS admin_user
                FROM `" . DB_PREFIX . "wallet_transaction` wt
                LEFT JOIN `" . DB_PREFIX . "customer` c
                    ON c.customer_id = wt.customer_id
                LEFT JOIN `" . DB_PREFIX . "user` u
                    ON u.user_id = wt.admin_user_id";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY wt.transaction_id DESC
                  LIMIT " .
                  (int)max(0, $filters['start'] ?? 0) . ", " .
                  (int)max(1, min(100, $filters['limit'] ?? 50));

        return $this->db->query($sql)->rows;
    }

    public function getTotalTransactions(array $filters = []): int
    {
        $where = [];

        if (!empty($filters['customer_id'])) {
            $where[] = "wt.customer_id = '" .
                (int)$filters['customer_id'] . "'";
        }

        if (!empty($filters['order_id'])) {
            $where[] = "wt.order_id = '" .
                (int)$filters['order_id'] . "'";
        }

        if (!empty($filters['type'])) {
            $where[] = "wt.type = '" .
                $this->db->escape($filters['type']) . "'";
        }

        if (!empty($filters['direction'])) {
            $where[] = "wt.direction = '" .
                $this->db->escape($filters['direction']) . "'";
        }

        $sql = "SELECT COUNT(*) AS total
                FROM `" . DB_PREFIX . "wallet_transaction` wt";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return (int)$this->db->query($sql)->row['total'];
    }
}
