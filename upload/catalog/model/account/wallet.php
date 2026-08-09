<?php
namespace Opencart\Catalog\Model\Account;

class Wallet extends \Opencart\System\Engine\Model
{
    public function ensureWallet(int $customer_id): int
    {
        if ($customer_id <= 0) {
            return 0;
        }

        $query = $this->db->query(
            "SELECT `wallet_id`
             FROM `" . DB_PREFIX . "wallet`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        if ($query->num_rows) {
            return (int)$query->row['wallet_id'];
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

    public function getBalance(int $customer_id): float
    {
        $query = $this->db->query(
            "SELECT `balance`
             FROM `" . DB_PREFIX . "wallet`
             WHERE `customer_id` = '" . (int)$customer_id . "'
               AND `status` = '1'
             LIMIT 1"
        );

        return $query->num_rows ? (float)$query->row['balance'] : 0.0;
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
             LIMIT " . (int)$start . ", " . (int)$limit
        );

        return $query->rows;
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
}
