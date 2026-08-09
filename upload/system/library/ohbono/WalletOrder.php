<?php
namespace Opencart\System\Library\Ohbono;

class WalletOrder
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function exists(int $order_id): bool
    {
        $query = $this->db->query(
            "SELECT `wallet_order_id`
             FROM `" . DB_PREFIX . "wallet_order`
             WHERE `order_id` = '" . (int)$order_id . "'
             AND `status` = '1'
             LIMIT 1"
        );

        return (bool)$query->num_rows;
    }

    public function get(int $order_id): array
    {
        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_order`
             WHERE `order_id` = '" . (int)$order_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function add(
        int $order_id,
        int $customer_id,
        float $amount,
        int $transaction_id
    ): int {
        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_order`
             SET `order_id` = '" . (int)$order_id . "',
                 `customer_id` = '" . (int)$customer_id . "',
                 `amount` = '" . (float)$amount . "',
                 `transaction_id` = '" . (int)$transaction_id . "',
                 `status` = '1',
                 `date_added` = NOW()"
        );

        return (int)$this->db->getLastId();
    }

    public function markRefunded(int $order_id): void
    {
        $this->db->query(
            "UPDATE `" . DB_PREFIX . "wallet_order`
             SET `status` = '2'
             WHERE `order_id` = '" . (int)$order_id . "'
             AND `status` = '1'"
        );
    }
}
