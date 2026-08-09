<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getBalance(int $customer_id): float
    {
        if ($customer_id <= 0) {
            return 0.0;
        }

        $query = $this->db->query(
            "SELECT COALESCE(balance, 0) AS balance
             FROM `" . DB_PREFIX . "wallet`
             WHERE customer_id = '" . (int)$customer_id . "'
             AND status = '1'
             LIMIT 1"
        );

        return $query->num_rows
            ? round((float)$query->row['balance'], 4)
            : 0.0;
    }
}
