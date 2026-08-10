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
            "SELECT balance
             FROM `" . DB_PREFIX . "wallet`
             WHERE customer_id = '" .
                (int)$customer_id . "'
             AND status = '1'
             LIMIT 1"
        );

        return $query->num_rows
            ? max(0.0, (float)$query->row['balance'])
            : 0.0;
    }

    public function hasSufficientBalance(
        int $customer_id,
        float $amount
    ): bool {
        if ($customer_id <= 0 || $amount <= 0) {
            return false;
        }

        return $this->getBalance($customer_id) >=
            round($amount, 4);
    }
}
