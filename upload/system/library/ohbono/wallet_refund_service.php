<?php
/**
 * OHBONO Wallet Refund Service
 *
 * Refunds a wallet payment associated with an order.
 *
 * Refunds are compensating credits. Existing debit transactions remain
 * immutable and the refund gets its own transaction record.
 */
class OhbonoWalletRefundService
{
    private $db;
    private $reversal;

    public function __construct($db, $reversal)
    {
        $this->db = $db;
        $this->reversal = $reversal;
    }

    public function refundOrderWalletPayment(
        int $customer_id,
        int $order_id,
        string $reference,
        string $reason,
        int $admin_user_id = 0
    ): int {
        if ($customer_id <= 0 ||
            $order_id <= 0 ||
            trim($reference) === '' ||
            trim($reason) === '') {
            throw new \InvalidArgumentException(
                'Complete refund information is required.'
            );
        }

        $transaction = $this->db->query(
            "SELECT transaction_id
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" .
                (int)$customer_id . "'
             AND order_id = '" .
                (int)$order_id . "'
             AND type = 'wallet_payment'
             AND direction = 'debit'
             ORDER BY transaction_id DESC
             LIMIT 1"
        );

        if (!$transaction->num_rows) {
            throw new \RuntimeException(
                'No wallet payment found for this order.'
            );
        }

        return $this->reversal->reverse(
            $customer_id,
            (int)$transaction->row['transaction_id'],
            $reference,
            $reason,
            $admin_user_id
        );
    }
}
