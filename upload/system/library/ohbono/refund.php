<?php
/**
 * OHBONO Wallet Refund Service
 *
 * Idempotent wallet refunds linked to the original order.
 *
 * Refunds are represented as wallet credits and can only be created once
 * for a given refund reference.
 */
class OhbonoWalletRefund
{
    private $db;
    private $wallet_service;

    public function __construct($db, $wallet_service)
    {
        $this->db = $db;
        $this->wallet_service = $wallet_service;
    }

    public function refund(
        int $order_id,
        int $customer_id,
        float $amount,
        string $reference,
        string $reason = ''
    ): array {
        if ($order_id <= 0 || $customer_id <= 0) {
            throw new \InvalidArgumentException(
                'Order and customer are required.'
            );
        }

        $amount = round($amount, 4);

        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                'Refund amount must be greater than zero.'
            );
        }

        $reference = trim($reference);

        if ($reference === '') {
            throw new \InvalidArgumentException(
                'A unique refund reference is required.'
            );
        }

        if (mb_strlen($reference) > 128) {
            throw new \InvalidArgumentException(
                'Refund reference is too long.'
            );
        }

        $existing = $this->db->query(
            "SELECT wo.wallet_order_id,
                    wo.transaction_id,
                    wo.amount
             FROM `" . DB_PREFIX . "wallet_order` wo
             WHERE wo.order_id = '" . (int)$order_id . "'
             AND wo.status = '2'
             AND wo.reference = '" . $this->db->escape($reference) . "'
             LIMIT 1"
        );

        if ($existing->num_rows) {
            return [
                'already_refunded' => true,
                'transaction_id' => (int)$existing->row['transaction_id'],
                'amount' => (float)$existing->row['amount']
            ];
        }

        $this->db->query("START TRANSACTION");

        try {
            /*
             * Lock the refund reference namespace. The unique database
             * constraint added by this commit provides the final duplicate
             * protection.
             */
            $duplicate = $this->db->query(
                "SELECT wallet_order_id,
                        transaction_id,
                        amount
                 FROM `" . DB_PREFIX . "wallet_order`
                 WHERE order_id = '" . (int)$order_id . "'
                 AND status = '2'
                 AND reference = '" . $this->db->escape($reference) . "'
                 LIMIT 1
                 FOR UPDATE"
            );

            if ($duplicate->num_rows) {
                $this->db->query("COMMIT");

                return [
                    'already_refunded' => true,
                    'transaction_id' => (int)$duplicate->row['transaction_id'],
                    'amount' => (float)$duplicate->row['amount']
                ];
            }

            $transaction_id = $this->wallet_service->credit(
                $customer_id,
                $amount,
                'order_refund',
                $reference,
                $reason,
                $order_id,
                0
            );

            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "wallet_order`
                 SET order_id = '" . (int)$order_id . "',
                     customer_id = '" . (int)$customer_id . "',
                     amount = '" . (float)$amount . "',
                     transaction_id = '" . (int)$transaction_id . "',
                     status = '2',
                     reference = '" . $this->db->escape($reference) . "',
                     date_added = NOW()"
            );

            $wallet_order_id = (int)$this->db->getLastId();

            $this->db->query("COMMIT");

            return [
                'already_refunded' => false,
                'wallet_order_id' => $wallet_order_id,
                'transaction_id' => (int)$transaction_id,
                'amount' => $amount
            ];
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
}
