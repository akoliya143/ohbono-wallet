<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletOrder extends \Opencart\System\Engine\Model
{
    public function get(int $order_id): array
    {
        $query = $this->db->query(
            "SELECT wo.*, c.firstname, c.lastname, c.email
             FROM `" . DB_PREFIX . "wallet_order` wo
             LEFT JOIN `" . DB_PREFIX . "customer` c
                ON (c.customer_id = wo.customer_id)
             WHERE wo.order_id = '" . (int)$order_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getByCustomer(int $customer_id, int $start = 0, int $limit = 50): array
    {
        $start = max(0, $start);
        $limit = max(1, min(100, $limit));

        $query = $this->db->query(
            "SELECT wo.*, o.order_status_id
             FROM `" . DB_PREFIX . "wallet_order` wo
             LEFT JOIN `" . DB_PREFIX . "order` o
                ON (o.order_id = wo.order_id)
             WHERE wo.customer_id = '" . (int)$customer_id . "'
             ORDER BY wo.wallet_order_id DESC
             LIMIT " . $start . ", " . $limit
        );

        return $query->rows;
    }
}
