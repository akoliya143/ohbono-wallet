<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletAudit extends \Opencart\System\Engine\Model
{
    public function getAudits(
        int $customer_id = 0,
        int $admin_user_id = 0,
        string $action = '',
        int $start = 0,
        int $limit = 100
    ): array {
        $start = max(0, $start);
        $limit = max(1, min(200, $limit));

        $where = [];

        if ($customer_id > 0) {
            $where[] = "a.customer_id = '" .
                (int)$customer_id . "'";
        }

        if ($admin_user_id > 0) {
            $where[] = "a.admin_user_id = '" .
                (int)$admin_user_id . "'";
        }

        if ($action !== '') {
            $where[] = "a.action = '" .
                $this->db->escape($action) . "'";
        }

        $condition = $where
            ? ' WHERE ' . implode(' AND ', $where)
            : '';

        return $this->db->query(
            "SELECT a.audit_id,
                    a.admin_user_id,
                    a.customer_id,
                    a.transaction_id,
                    a.action,
                    a.reason,
                    a.date_added,
                    CONCAT(
                        c.firstname, ' ', c.lastname
                    ) AS customer,
                    c.email,
                    CONCAT(
                        u.firstname, ' ', u.lastname
                    ) AS admin_user
             FROM `" . DB_PREFIX . "wallet_admin_audit` a
             LEFT JOIN `" . DB_PREFIX . "customer` c
                ON c.customer_id = a.customer_id
             LEFT JOIN `" . DB_PREFIX . "user` u
                ON u.user_id = a.admin_user_id
             " . $condition . "
             ORDER BY a.audit_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }
}
