<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

use Opencart\System\Library\Ohbono\WalletFactory;
use Opencart\System\Library\Ohbono\WalletTransaction;
use RuntimeException;

class WalletCustomer extends \Opencart\System\Engine\Model
{
    public function getWallets(array $data = []): array
    {
        $sql = "SELECT w.*, c.firstname, c.lastname, c.email
                FROM `" . DB_PREFIX . "wallet` w
                INNER JOIN `" . DB_PREFIX . "customer` c
                    ON (c.customer_id = w.customer_id)
                WHERE 1";

        if (!empty($data['filter_customer'])) {
            $filter = $this->db->escape($data['filter_customer']);

            $sql .= " AND (
                CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $filter . "%'
                OR c.email LIKE '%" . $filter . "%'
                OR c.customer_id = '" . (int)$data['filter_customer'] . "'
            )";
        }

        $sql .= " ORDER BY c.firstname, c.lastname";

        $start = max(0, (int)($data['start'] ?? 0));
        $limit = max(1, min(100, (int)($data['limit'] ?? 25)));

        $sql .= " LIMIT " . $start . ", " . $limit;

        return $this->db->query($sql)->rows;
    }

    public function getTotalWallets(array $data = []): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM `" . DB_PREFIX . "wallet` w
                INNER JOIN `" . DB_PREFIX . "customer` c
                    ON (c.customer_id = w.customer_id)
                WHERE 1";

        if (!empty($data['filter_customer'])) {
            $filter = $this->db->escape($data['filter_customer']);

            $sql .= " AND (
                CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $filter . "%'
                OR c.email LIKE '%" . $filter . "%'
                OR c.customer_id = '" . (int)$data['filter_customer'] . "'
            )";
        }

        return (int)$this->db->query($sql)->row['total'];
    }

    public function getWallet(int $customer_id): array
    {
        $query = $this->db->query(
            "SELECT w.*, c.firstname, c.lastname, c.email
             FROM `" . DB_PREFIX . "wallet` w
             INNER JOIN `" . DB_PREFIX . "customer` c
                ON (c.customer_id = w.customer_id)
             WHERE w.customer_id = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getTransactions(int $customer_id, int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));

        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'
             ORDER BY transaction_id DESC
             LIMIT " . $limit
        );

        return $query->rows;
    }

    public function adjust(
        int $customer_id,
        string $direction,
        float $amount,
        string $reference,
        string $comment
    ): int {
        if ($customer_id <= 0 || $amount <= 0) {
            throw new RuntimeException('Invalid wallet adjustment.');
        }

        $factory = new WalletFactory($this->registry);
        $service = $factory->service();

        if (!$service->isEnabled()) {
            throw new RuntimeException('Wallet is disabled.');
        }

        if ($direction === 'credit') {
            return $service->credit(
                $customer_id,
                $amount,
                WalletTransaction::TYPE_ADMIN_CREDIT,
                $comment !== '' ? $comment : 'Admin wallet credit',
                $reference,
                0,
                (int)$this->user->getId()
            );
        }

        if ($direction === 'debit') {
            return $service->debit(
                $customer_id,
                $amount,
                WalletTransaction::TYPE_ADMIN_DEBIT,
                $comment !== '' ? $comment : 'Admin wallet debit',
                $reference,
                0,
                (int)$this->user->getId()
            );
        }

        throw new RuntimeException('Invalid wallet adjustment direction.');
    }
}
