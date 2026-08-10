<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletEmailQueue extends \Opencart\System\Engine\Model
{
    public function getQueues(
        int $start = 0,
        int $limit = 100,
        string $status = ''
    ): array {
        $start = max(0, $start);
        $limit = max(1, min(200, $limit));

        $where = '';

        $allowed = [
            'pending',
            'processing',
            'sent',
            'failed'
        ];

        if (in_array($status, $allowed, true)) {
            $where = " AND q.status = '" .
                $this->db->escape($status) . "'";
        }

        return $this->db->query(
            "SELECT q.queue_id,
                    q.customer_id,
                    q.transaction_id,
                    q.subject,
                    q.status,
                    q.attempts,
                    q.available_at,
                    q.date_added,
                    q.date_started,
                    q.date_sent,
                    q.last_error,
                    c.email,
                    CONCAT(
                        c.firstname,
                        ' ',
                        c.lastname
                    ) AS customer
             FROM `" . DB_PREFIX . "wallet_email_queue` q
             LEFT JOIN `" . DB_PREFIX . "customer` c
                ON c.customer_id = q.customer_id
             WHERE 1=1 " . $where . "
             ORDER BY q.queue_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }

    public function getStats(): array
    {
        $rows = $this->db->query(
            "SELECT status, COUNT(*) AS total
             FROM `" . DB_PREFIX . "wallet_email_queue`
             GROUP BY status"
        )->rows;

        $stats = [
            'pending' => 0,
            'processing' => 0,
            'sent' => 0,
            'failed' => 0
        ];

        foreach ($rows as $row) {
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] =
                    (int)$row['total'];
            }
        }

        return $stats;
    }

    public function retry(int $queue_id): bool
    {
        if ($queue_id <= 0) {
            return false;
        }

        $this->db->query(
            "UPDATE `" . DB_PREFIX . "wallet_email_queue`
             SET status = 'pending',
                 available_at = NOW(),
                 last_error = NULL
             WHERE queue_id = '" . (int)$queue_id . "'
             AND status = 'failed'"
        );

        return $this->db->countAffected() > 0;
    }
}
