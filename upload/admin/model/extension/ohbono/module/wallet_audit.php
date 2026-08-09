<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletAudit extends \Opencart\System\Engine\Model
{
    public function getAudits(
        array $filters = [],
        int $start = 0,
        int $limit = 50
    ): array {
        $sql = "SELECT wa.*,
                       CONCAT(u.firstname, ' ', u.lastname) AS admin_name
                FROM `" . DB_PREFIX . "wallet_audit` wa
                LEFT JOIN `" . DB_PREFIX . "user` u
                    ON u.user_id = wa.admin_user_id
                WHERE 1";

        $sql .= $this->buildWhere($filters);

        $sql .= " ORDER BY wa.audit_id DESC";

        $start = max(0, $start);
        $limit = max(1, min(10000, $limit));

        $sql .= " LIMIT " . $start . ", " . $limit;

        return $this->db->query($sql)->rows;
    }

    public function getTotalAudits(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM `" . DB_PREFIX . "wallet_audit` wa
                WHERE 1";

        $sql .= $this->buildWhere($filters);

        return (int)$this->db->query($sql)->row['total'];
    }

    private function buildWhere(array $filters): string
    {
        $sql = '';

        $customer_id = (int)($filters['customer_id'] ?? 0);
        $admin_id = (int)($filters['admin_id'] ?? 0);
        $action = trim((string)($filters['action'] ?? ''));
        $date_start = trim((string)($filters['date_start'] ?? ''));
        $date_end = trim((string)($filters['date_end'] ?? ''));

        if ($customer_id > 0) {
            $sql .= " AND wa.customer_id = '" . $customer_id . "'";
        }

        if ($admin_id > 0) {
            $sql .= " AND wa.admin_user_id = '" . $admin_id . "'";
        }

        if (in_array($action, ['admin_credit', 'admin_debit'], true)) {
            $sql .= " AND wa.action = '" . $this->db->escape($action) . "'";
        }

        if ($this->isDate($date_start)) {
            $sql .= " AND wa.date_added >= '" .
                $this->db->escape($date_start) . " 00:00:00'";
        }

        if ($this->isDate($date_end)) {
            $sql .= " AND wa.date_added <= '" .
                $this->db->escape($date_end) . " 23:59:59'";
        }

        return $sql;
    }

    private function isDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date;
    }
}
