<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

class WalletPayment extends \Opencart\System\Engine\Model
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

    public function getPaymentTransaction(
        int $customer_id,
        string $reference
    ): array {
        if ($customer_id <= 0 || trim($reference) === '') {
            return [];
        }

        $query = $this->db->query(
            "SELECT transaction_id,
                    customer_id,
                    amount,
                    balance_after,
                    reference,
                    order_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" .
                (int)$customer_id . "'
             AND reference = '" .
                $this->db->escape(trim($reference)) . "'
             AND type = 'wallet_payment'
             LIMIT 1"
        );

        return $query->num_rows
            ? $query->row
            : [];
    }
}
