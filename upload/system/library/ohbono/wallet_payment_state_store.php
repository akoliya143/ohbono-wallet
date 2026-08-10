<?php
/**
 * OHBONO Wallet Payment State Store
 *
 * Persists reconciliation state independently from the financial transaction
 * ledger. The ledger remains immutable.
 */
class OhbonoWalletPaymentStateStore
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function ensureState(
        int $order_id,
        int $customer_id,
        string $state,
        float $wallet_amount,
        float $remaining_amount
    ): int {
        if ($order_id <= 0 || $customer_id <= 0) {
            throw new \InvalidArgumentException(
                'Order and customer are required.'
            );
        }

        $allowed = [
            'awaiting_payment',
            'wallet_captured',
            'partially_paid',
            'paid',
            'reconciliation_required',
            'reversed',
            'cancelled'
        ];

        if (!in_array($state, $allowed, true)) {
            throw new \InvalidArgumentException(
                'Invalid wallet payment state.'
            );
        }

        $wallet_amount = round(
            max(0.0, $wallet_amount),
            4
        );

        $remaining_amount = round(
            max(0.0, $remaining_amount),
            4
        );

        $existing = $this->db->query(
            "SELECT wallet_payment_state_id
             FROM `" . DB_PREFIX . "wallet_payment_state`
             WHERE order_id = '" .
                (int)$order_id . "'
             LIMIT 1"
        );

        if ($existing->num_rows) {
            $id = (int)$existing->row[
                'wallet_payment_state_id'
            ];

            $this->db->query(
                "UPDATE `" .
                DB_PREFIX . "wallet_payment_state`
                 SET state = '" .
                    $this->db->escape($state) . "',
                     wallet_amount = '" .
                    (float)$wallet_amount . "',
                     remaining_amount = '" .
                    (float)$remaining_amount . "',
                     date_modified = NOW()
                 WHERE wallet_payment_state_id = '" .
                    $id . "'"
            );

            return $id;
        }

        $this->db->query(
            "INSERT INTO `" .
            DB_PREFIX . "wallet_payment_state`
             SET order_id = '" .
                (int)$order_id . "',
                 customer_id = '" .
                (int)$customer_id . "',
                 state = '" .
                $this->db->escape($state) . "',
                 wallet_amount = '" .
                (float)$wallet_amount . "',
                 remaining_amount = '" .
                (float)$remaining_amount . "',
                 date_added = NOW(),
                 date_modified = NOW()"
        );

        return (int)$this->db->getLastId();
    }

    public function getState(int $order_id): array
    {
        if ($order_id <= 0) {
            return [];
        }

        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_payment_state`
             WHERE order_id = '" .
                (int)$order_id . "'
             LIMIT 1"
        );

        return $query->num_rows
            ? $query->row
            : [];
    }
}
