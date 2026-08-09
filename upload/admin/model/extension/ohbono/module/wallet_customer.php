<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletCustomer extends \Opencart\System\Engine\Model
{
    public function getCustomers(array $data = []): array
    {
        $sql = "SELECT c.customer_id,
                       c.firstname,
                       c.lastname,
                       c.email,
                       COALESCE(w.balance, 0) AS balance,
                       COALESCE(w.status, 1) AS wallet_status,
                       w.date_modified
                FROM `" . DB_PREFIX . "customer` c
                LEFT JOIN `" . DB_PREFIX . "wallet` w
                    ON w.customer_id = c.customer_id
                WHERE 1";

        $filter = trim((string)($data['filter'] ?? ''));

        if ($filter !== '') {
            $escaped = $this->db->escape($filter);

            $sql .= " AND (
                CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $escaped . "%'
                OR c.email LIKE '%" . $escaped . "%'
                OR c.customer_id = '" . (int)$filter . "'
            )";
        }

        $sql .= " ORDER BY c.customer_id DESC";

        $start = max(0, (int)($data['start'] ?? 0));
        $limit = max(1, min(100, (int)($data['limit'] ?? 25)));

        $sql .= " LIMIT " . $start . ", " . $limit;

        return $this->db->query($sql)->rows;
    }

    public function getTotalCustomers(string $filter = ''): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM `" . DB_PREFIX . "customer` c
                WHERE 1";

        $filter = trim($filter);

        if ($filter !== '') {
            $escaped = $this->db->escape($filter);

            $sql .= " AND (
                CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $escaped . "%'
                OR c.email LIKE '%" . $escaped . "%'
                OR c.customer_id = '" . (int)$filter . "'
            )";
        }

        return (int)$this->db->query($sql)->row['total'];
    }

    public function getCustomer(int $customer_id): array
    {
        if ($customer_id <= 0) {
            return [];
        }

        $query = $this->db->query(
            "SELECT c.customer_id,
                    c.firstname,
                    c.lastname,
                    c.email,
                    COALESCE(w.balance, 0) AS balance,
                    COALESCE(w.status, 1) AS status
             FROM `" . DB_PREFIX . "customer` c
             LEFT JOIN `" . DB_PREFIX . "wallet` w
                ON w.customer_id = c.customer_id
             WHERE c.customer_id = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getSummary(int $customer_id): array
    {
        $summary = [
            'credited' => 0.0,
            'debited' => 0.0,
            'count' => 0
        ];

        if ($customer_id <= 0) {
            return $summary;
        }

        $query = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END), 0) AS credited,
                COALESCE(SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END), 0) AS debited,
                COUNT(*) AS transaction_count
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'"
        );

        if ($query->num_rows) {
            $summary['credited'] = round((float)$query->row['credited'], 4);
            $summary['debited'] = round((float)$query->row['debited'], 4);
            $summary['count'] = (int)$query->row['transaction_count'];
        }

        return $summary;
    }

    public function getTransactions(
        int $customer_id,
        int $start = 0,
        int $limit = 20
    ): array {
        if ($customer_id <= 0) {
            return [];
        }

        $start = max(0, $start);
        $limit = max(1, min(100, $limit));

        return $this->db->query(
            "SELECT transaction_id,
                    type,
                    direction,
                    amount,
                    balance_after,
                    reference,
                    order_id,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'
             ORDER BY transaction_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }

    public function adjust(
        int $customer_id,
        string $direction,
        float $amount,
        string $reference,
        string $comment,
        int $admin_user_id
    ): array {
        if (!in_array($direction, ['credit', 'debit'], true)) {
            throw new \InvalidArgumentException('Invalid wallet adjustment direction.');
        }

        $this->load->library('ohbono/wallet_service');

        $type = $direction === 'credit'
            ? 'admin_credit'
            : 'admin_debit';

        $transaction_id = $direction === 'credit'
            ? $this->wallet_service->credit(
                $customer_id,
                $amount,
                $type,
                $reference,
                $comment,
                0,
                $admin_user_id
            )
            : $this->wallet_service->debit(
                $customer_id,
                $amount,
                $type,
                $reference,
                $comment,
                0,
                $admin_user_id
            );

        return [
            'transaction_id' => $transaction_id,
            'balance' => $this->wallet_service->getBalance($customer_id)
        ];
    }
}
