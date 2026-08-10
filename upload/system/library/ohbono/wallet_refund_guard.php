<?php
/**
 * OHBONO Wallet Refund Guard
 *
 * Ensures a wallet payment is refunded only once for the requested order
 * reference.
 */
class OhbonoWalletRefundGuard
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function assertNotAlreadyRefunded(
        int $customer_id,
        int $order_id,
        string $reference
    ): void {
        if ($customer_id <= 0 || $order_id <= 0) {
            throw new \InvalidArgumentException(
                'Customer and order are required.'
            );
        }

        $query = $this->db->query(
            "SELECT transaction_id
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" .
                (int)$customer_id . "'
             AND order_id = '" .
                (int)$order_id . "'
             AND type = 'wallet_reversal'
             AND direction = 'credit'
             AND reference = '" .
                $this->db->escape(
                    trim($reference)
                ) . "'
             LIMIT 1"
        );

        if ($query->num_rows) {
            throw new \RuntimeException(
                'This wallet refund has already been processed.'
            );
        }
    }
}
