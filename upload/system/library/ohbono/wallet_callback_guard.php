<?php
/**
 * OHBONO duplicate callback guard.
 *
 * Financial callback processing must be idempotent. A stable server-generated
 * reference identifies the payment attempt.
 */
class OhbonoWalletCallbackGuard
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findExisting(
        int $customer_id,
        string $reference
    ): array {
        if ($customer_id <= 0 || trim($reference) === '') {
            return [];
        }

        $query = $this->db->query(
            "SELECT transaction_id,
                    amount,
                    balance_after,
                    order_id,
                    reference,
                    date_added
             FROM `" .
                DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" .
                (int)$customer_id . "'
             AND reference = '" .
                $this->db->escape(
                    trim($reference)
                ) . "'
             AND type = 'wallet_payment'
             AND direction = 'debit'
             LIMIT 1"
        );

        return $query->num_rows
            ? $query->row
            : [];
    }
}
