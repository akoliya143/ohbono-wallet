<?php
/**
 * OHBONO Wallet Email Queue Service.
 *
 * Email is queued after a financial transaction is committed.
 * Queue records are idempotent per wallet transaction and notification type.
 */
class OhbonoWalletEmailQueueService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function queueTransactionEmail(int $transaction_id): int
    {
        if ($transaction_id <= 0) {
            return 0;
        }

        $transaction = $this->db->query(
            "SELECT transaction_id, customer_id, type, direction, amount, order_id
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE transaction_id = '" . (int)$transaction_id . "'
             LIMIT 1"
        );

        if (!$transaction->num_rows) {
            return 0;
        }

        $row = $transaction->row;
        $preference = new \OhbonoWalletPreferenceService($this->db);
        $preferences = $preference->get((int)$row['customer_id']);

        if (!$preferences['email_enabled']) {
            return 0;
        }

        $preference_key = 'email_debit';

        if ($row['type'] === 'order_refund') {
            $preference_key = 'email_refund';
        } elseif ($row['direction'] === 'credit') {
            $preference_key = 'email_credit';
        }

        if (empty($preferences[$preference_key])) {
            return 0;
        }

        $subject = 'OHBONO Wallet Transaction #' .
            (int)$row['transaction_id'];

        if ($row['direction'] === 'credit') {
            $message = 'Your OHBONO Wallet was credited with ' .
                number_format((float)$row['amount'], 2);
        } else {
            $message = 'Your OHBONO Wallet was debited by ' .
                number_format((float)$row['amount'], 2);
        }

        if ($row['order_id']) {
            $message .= ' for order #' . (int)$row['order_id'] . '.';
        } else {
            $message .= '.';
        }

        $existing = $this->db->query(
            "SELECT queue_id
             FROM `" . DB_PREFIX . "wallet_email_queue`
             WHERE transaction_id = '" . (int)$transaction_id . "'
             AND notification_type = 'wallet_transaction'
             LIMIT 1"
        );

        if ($existing->num_rows) {
            return (int)$existing->row['queue_id'];
        }

        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_email_queue`
             SET customer_id = '" . (int)$row['customer_id'] . "',
                 transaction_id = '" . (int)$transaction_id . "',
                 notification_type = 'wallet_transaction',
                 subject = '" . $this->db->escape($subject) . "',
                 message = '" . $this->db->escape($message) . "',
                 status = 'pending',
                 attempts = '0',
                 available_at = NOW(),
                 date_added = NOW()"
        );

        return (int)$this->db->getLastId();
    }
}
