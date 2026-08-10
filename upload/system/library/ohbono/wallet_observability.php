<?php
/**
 * OHBONO Wallet Observability
 *
 * Financial mutations should be observable without exposing sensitive data.
 * This logger records structured operational events only.
 */
class OhbonoWalletObservability
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function log(
        string $event,
        int $customer_id = 0,
        int $order_id = 0,
        int $transaction_id = 0,
        string $status = 'info',
        string $message = ''
    ): void {
        $event = trim($event);
        $status = trim($status);
        $message = trim($message);

        if ($event === '') {
            return;
        }

        if (strlen($event) > 80) {
            $event = substr($event, 0, 80);
        }

        if (strlen($status) > 30) {
            $status = substr($status, 0, 30);
        }

        if (strlen($message) > 500) {
            $message = substr($message, 0, 500);
        }

        $this->db->query(
            "INSERT INTO `" .
            DB_PREFIX . "wallet_operation_log`
             SET event = '" .
                $this->db->escape($event) . "',
                 customer_id = '" .
                (int)$customer_id . "',
                 order_id = '" .
                (int)$order_id . "',
                 transaction_id = '" .
                (int)$transaction_id . "',
                 status = '" .
                $this->db->escape($status) . "',
                 message = '" .
                $this->db->escape($message) . "',
                 date_added = NOW()"
        );
    }
}
