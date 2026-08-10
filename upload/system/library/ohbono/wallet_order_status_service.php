<?php
/**
 * OHBONO Wallet Order Status Service
 *
 * Records the relationship between an order and its wallet transaction.
 *
 * This creates a durable payment linkage without changing OpenCart order
 * status automatically. Status transitions remain under the checkout/order
 * workflow.
 */
class OhbonoWalletOrderStatusService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getWalletPayment(
        int $order_id
    ): array {
        if ($order_id <= 0) {
            return [];
        }

        $query = $this->db->query(
            "SELECT transaction_id,
                    customer_id,
                    amount,
                    balance_before,
                    balance_after,
                    reference,
                    date_added
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE order_id = '" .
                (int)$order_id . "'
             AND type = 'wallet_payment'
             AND direction = 'debit'
             ORDER BY transaction_id DESC
             LIMIT 1"
        );

        return $query->num_rows
            ? $query->row
            : [];
    }
}
