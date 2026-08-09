<?php
namespace Opencart\System\Library\Ohbono;

/**
 * Database access for the wallet domain.
 */
class WalletRepository
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function exists(int $customer_id): bool
    {
        $query = $this->db->query(
            "SELECT `wallet_id`
             FROM `" . DB_PREFIX . "wallet`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows > 0;
    }

    public function create(int $customer_id): int
    {
        if ($customer_id <= 0) {
            throw new WalletException('Invalid customer ID.');
        }

        if ($this->exists($customer_id)) {
            $wallet = $this->getByCustomerId($customer_id);

            return (int)$wallet['wallet_id'];
        }

        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet`
             SET `customer_id` = '" . (int)$customer_id . "',
                 `balance` = '0.0000',
                 `status` = '1',
                 `date_added` = NOW(),
                 `date_modified` = NOW()"
        );

        return (int)$this->db->getLastId();
    }

    public function getByCustomerId(int $customer_id): array
    {
        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getForUpdate(int $customer_id): array
    {
        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             LIMIT 1
             FOR UPDATE"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function updateBalance(int $wallet_id, float $balance): void
    {
        $this->db->query(
            "UPDATE `" . DB_PREFIX . "wallet`
             SET `balance` = '" . $this->db->escape(number_format($balance, 4, '.', '')) . "',
                 `date_modified` = NOW()
             WHERE `wallet_id` = '" . (int)$wallet_id . "'"
        );
    }

    public function insertTransaction(array $data): int
    {
        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_transaction`
             SET `wallet_id` = '" . (int)$data['wallet_id'] . "',
                 `customer_id` = '" . (int)$data['customer_id'] . "',
                 `order_id` = '" . (int)($data['order_id'] ?? 0) . "',
                 `type` = '" . $this->db->escape($data['type']) . "',
                 `direction` = '" . $this->db->escape($data['direction']) . "',
                 `amount` = '" . $this->db->escape(number_format((float)$data['amount'], 4, '.', '')) . "',
                 `balance_before` = '" . $this->db->escape(number_format((float)$data['balance_before'], 4, '.', '')) . "',
                 `balance_after` = '" . $this->db->escape(number_format((float)$data['balance_after'], 4, '.', '')) . "',
                 `reference` = '" . $this->db->escape($data['reference'] ?? '') . "',
                 `comment` = '" . $this->db->escape($data['comment'] ?? '') . "',
                 `created_by` = '" . (int)($data['created_by'] ?? 0) . "',
                 `date_added` = NOW()"
        );

        return (int)$this->db->getLastId();
    }

    public function getTransactions(int $customer_id, int $start = 0, int $limit = 20): array
    {
        $start = max(0, $start);
        $limit = max(1, min(100, $limit));

        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             ORDER BY `transaction_id` DESC
             LIMIT " . (int)$start . "," . (int)$limit
        );

        return $query->rows;
    }

    public function getTransaction(int $transaction_id): array
    {
        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE `transaction_id` = '" . (int)$transaction_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getTransactionCount(int $customer_id): int
    {
        $query = $this->db->query(
            "SELECT COUNT(*) AS `total`
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE `customer_id` = '" . (int)$customer_id . "'"
        );

        return (int)$query->row['total'];
    }

    public function createWalletOrder(
        int $order_id,
        int $customer_id,
        int $transaction_id,
        float $wallet_used
    ): int {
        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_order`
             SET `order_id` = '" . (int)$order_id . "',
                 `customer_id` = '" . (int)$customer_id . "',
                 `transaction_id` = '" . (int)$transaction_id . "',
                 `wallet_used` = '" . $this->db->escape(number_format($wallet_used, 4, '.', '')) . "',
                 `date_added` = NOW()"
        );

        return (int)$this->db->getLastId();
    }

    public function getWalletOrderByOrderId(int $order_id): array
    {
        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_order`
             WHERE `order_id` = '" . (int)$order_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getSetting(string $key, $default = null)
    {
        $query = $this->db->query(
            "SELECT `setting_value`
             FROM `" . DB_PREFIX . "wallet_setting`
             WHERE `setting_key` = '" . $this->db->escape($key) . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row['setting_value'] : $default;
    }

    public function setSetting(string $key, string $value): void
    {
        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_setting`
             SET `setting_key` = '" . $this->db->escape($key) . "',
                 `setting_value` = '" . $this->db->escape($value) . "',
                 `date_modified` = NOW()
             ON DUPLICATE KEY UPDATE
                 `setting_value` = VALUES(`setting_value`),
                 `date_modified` = NOW()"
        );
    }
}
