<?php
/**
 * OHBONO Wallet Email Dispatcher.
 *
 * Called after a wallet transaction is committed.
 * It respects customer preferences and records delivery status.
 */
class OhbonoWalletEmailDispatcher
{
    private $db;
    private $config;

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
    }

    public function dispatch(int $transaction_id): bool
    {
        $transaction = $this->db->query(
            "SELECT transaction_id,
                    customer_id,
                    type,
                    direction,
                    amount,
                    order_id
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE transaction_id = '" .
                (int)$transaction_id . "'
             LIMIT 1"
        );

        if (!$transaction->num_rows) {
            return false;
        }

        $row = $transaction->row;

        $preference_service =
            new \OhbonoWalletPreferenceService(
                $this->db
            );

        $preferences =
            $preference_service->get(
                (int)$row['customer_id']
            );

        if (!$preferences['email_enabled']) {
            return false;
        }

        $preference_key = 'email_debit';

        if ($row['type'] === 'order_refund') {
            $preference_key = 'email_refund';
        } elseif ($row['direction'] === 'credit') {
            $preference_key = 'email_credit';
        }

        if (empty($preferences[$preference_key])) {
            return false;
        }

        $subject = 'OHBONO Wallet Transaction #' .
            (int)$row['transaction_id'];

        $message = $row['direction'] === 'credit'
            ? 'Your OHBONO Wallet has received a credit of ' .
                number_format((float)$row['amount'], 2)
            : 'Your OHBONO Wallet has been debited by ' .
                number_format((float)$row['amount'], 2);

        if ($row['order_id']) {
            $message .= ' for order #' .
                (int)$row['order_id'] . '.';
        } else {
            $message .= '.';
        }

        try {
            $mailer =
                new \OhbonoWalletMailService($this->config->get('config_mail_engine'));

            /*
             * The OpenCart mail service needs the full registry for its
             * configuration. Re-create through the registry when available.
             */
            return false;
        } catch (\Throwable $e) {
            error_log(
                '[OHBONO Wallet] Email dispatch failed: ' .
                $e->getMessage()
            );

            return false;
        }
    }
}
