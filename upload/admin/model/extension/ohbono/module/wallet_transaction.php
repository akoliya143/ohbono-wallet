<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletTransaction extends \Opencart\System\Engine\Model
{
    public function getTransactions(array $data = []): array
    {
        $sql = "SELECT wt.*, c.firstname, c.lastname, c.email
                FROM `" . DB_PREFIX . "wallet_transaction` wt
                LEFT JOIN `" . DB_PREFIX . "customer` c
                    ON (c.customer_id = wt.customer_id)
                WHERE 1";

        $sql .= $this->buildWhere($data);
        $sql .= " ORDER BY wt.transaction_id DESC";

        $start = max(0, (int)($data['start'] ?? 0));
        $limit = max(1, min(100, (int)($data['limit'] ?? 25)));

        $sql .= " LIMIT " . $start . ", " . $limit;

        return $this->db->query($sql)->rows;
    }

    public function getTotalTransactions(array $data = []): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM `" . DB_PREFIX . "wallet_transaction` wt
                LEFT JOIN `" . DB_PREFIX . "customer` c
                    ON (c.customer_id = wt.customer_id)
                WHERE 1";

        $sql .= $this->buildWhere($data);

        return (int)$this->db->query($sql)->row['total'];
    }

    private function buildWhere(array $data): string
    {
        $sql = '';

        if (!empty($data['filter_customer'])) {
            $filter = $this->db->escape($data['filter_customer']);

            $sql .= " AND (
                CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $filter . "%'
                OR c.email LIKE '%" . $filter . "%'
                OR c.customer_id = '" . (int)$data['filter_customer'] . "'
            )";
        }

        if (!empty($data['filter_type'])) {
            $sql .= " AND wt.type = '" . $this->db->escape($data['filter_type']) . "'";
        }

        if (in_array($data['filter_direction'] ?? '', ['credit', 'debit'], true)) {
            $sql .= " AND wt.direction = '" . $this->db->escape($data['filter_direction']) . "'";
        }

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND wt.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_date_start'])) {
            $date = $this->db->escape($data['filter_date_start']);
            $sql .= " AND DATE(wt.date_added) >= '" . $date . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $date = $this->db->escape($data['filter_date_end']);
            $sql .= " AND DATE(wt.date_added) <= '" . $date . "'";
        }

        return $sql;
    }
}
