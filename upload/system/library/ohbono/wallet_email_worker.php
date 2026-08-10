<?php
/**
 * OHBONO Wallet Email Worker
 *
 * Batch 0073 adds stale-processing recovery and queue maintenance.
 */
class OhbonoWalletEmailWorker
{
    private $db;
    private $registry;
    private $config;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
    }

    public function process(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));

        $this->recoverStaleProcessing();

        $result = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0
        ];

        for ($i = 0; $i < $limit; $i++) {
            $queue_id = $this->claimNext();

            if (!$queue_id) {
                break;
            }

            $result['processed']++;

            $status = $this->sendOne($queue_id);

            if ($status === 'sent') {
                $result['sent']++;
            } elseif ($status === 'failed') {
                $result['failed']++;
            } else {
                $result['skipped']++;
            }
        }

        return $result;
    }

    public function recoverStaleProcessing(int $minutes = 15): int
    {
        $minutes = max(5, min(120, $minutes));

        $this->db->query(
            "UPDATE `" . DB_PREFIX . "wallet_email_queue`
             SET status = 'pending',
                 available_at = NOW(),
                 last_error = CONCAT(
                    COALESCE(last_error, ''),
                    CASE
                        WHEN COALESCE(last_error, '') = '' THEN ''
                        ELSE ' '
                    END,
                    'Recovered stale processing item.'
                 )
             WHERE status = 'processing'
             AND date_started IS NOT NULL
             AND date_started < DATE_SUB(
                NOW(),
                INTERVAL " . (int)$minutes . " MINUTE
             )"
        );

        return $this->db->countAffected();
    }

    private function claimNext(): int
    {
        $this->db->query("START TRANSACTION");

        try {
            $query = $this->db->query(
                "SELECT queue_id
                 FROM `" . DB_PREFIX . "wallet_email_queue`
                 WHERE status = 'pending'
                 AND available_at <= NOW()
                 ORDER BY queue_id ASC
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$query->num_rows) {
                $this->db->query("COMMIT");
                return 0;
            }

            $queue_id = (int)$query->row['queue_id'];

            $this->db->query(
                "UPDATE `" . DB_PREFIX . "wallet_email_queue`
                 SET status = 'processing',
                     attempts = attempts + 1,
                     date_started = NOW()
                 WHERE queue_id = '" . $queue_id . "'
                 AND status = 'pending'"
            );

            if (!$this->db->countAffected()) {
                $this->db->query("ROLLBACK");
                return 0;
            }

            $this->db->query("COMMIT");
            return $queue_id;
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            error_log(
                '[OHBONO Wallet] Queue claim failed: ' .
                $e->getMessage()
            );
            return 0;
        }
    }

    private function sendOne(int $queue_id): string
    {
        $query = $this->db->query(
            "SELECT q.*,
                    c.email,
                    c.firstname,
                    c.lastname
             FROM `" . DB_PREFIX . "wallet_email_queue` q
             INNER JOIN `" . DB_PREFIX . "customer` c
                ON c.customer_id = q.customer_id
             WHERE q.queue_id = '" . (int)$queue_id . "'
             AND q.status = 'processing'
             LIMIT 1"
        );

        if (!$query->num_rows) {
            return 'skipped';
        }

        $row = $query->row;

        try {
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException(
                    'Invalid customer email address.'
                );
            }

            $mail = new \Opencart\System\Library\Mail(
                $this->config->get('config_mail_engine')
            );

            $mail->parameter =
                $this->config->get('config_mail_parameter');
            $mail->smtp_hostname =
                $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username =
                $this->config->get('config_mail_smtp_username');
            $mail->smtp_password =
                $this->config->get('config_mail_smtp_password');
            $mail->smtp_port =
                $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout =
                $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($row['email']);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject($row['subject']);
            $mail->setText($row['message']);
            $mail->send();

            $this->db->query(
                "UPDATE `" . DB_PREFIX . "wallet_email_queue`
                 SET status = 'sent',
                     date_sent = NOW(),
                     last_error = NULL
                 WHERE queue_id = '" . (int)$queue_id . "'
                 AND status = 'processing'"
            );

            return 'sent';
        } catch (\Throwable $e) {
            $this->scheduleRetry($queue_id, $e->getMessage());
            return 'failed';
        }
    }

    private function scheduleRetry(
        int $queue_id,
        string $error
    ): void {
        $query = $this->db->query(
            "SELECT attempts
             FROM `" . DB_PREFIX . "wallet_email_queue`
             WHERE queue_id = '" . (int)$queue_id . "'
             LIMIT 1"
        );

        $attempts = $query->num_rows
            ? (int)$query->row['attempts']
            : 1;

        if ($attempts >= 5) {
            $this->db->query(
                "UPDATE `" . DB_PREFIX . "wallet_email_queue`
                 SET status = 'failed',
                     last_error = '" .
                    $this->db->escape(
                        substr($error, 0, 1000)
                    ) . "'
                 WHERE queue_id = '" . (int)$queue_id . "'"
            );
            return;
        }

        $delay = min(
            3600,
            60 * (2 ** max(0, $attempts - 1))
        );

        $this->db->query(
            "UPDATE `" . DB_PREFIX . "wallet_email_queue`
             SET status = 'pending',
                 available_at = DATE_ADD(
                    NOW(),
                    INTERVAL " . (int)$delay . " SECOND
                 ),
                 last_error = '" .
                $this->db->escape(
                    substr($error, 0, 1000)
                ) . "'
             WHERE queue_id = '" . (int)$queue_id . "'"
        );
    }
}
