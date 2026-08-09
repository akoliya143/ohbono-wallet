<?php
/**
 * OHBONO Wallet Checkout Finalizer
 *
 * Commit 0041:
 * - finalizes a reserved wallet transaction against an OpenCart order
 * - prevents the same reservation from being attached to multiple orders
 * - restores wallet funds when the order cannot be finalized
 */
class OhbonoWalletCheckout
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function finalize(
        int $customer_id,
        int $order_id,
        int $transaction_id,
        float $amount,
        string $reference
    ): array {
        if ($customer_id <= 0 || $order_id <= 0 || $transaction_id <= 0) {
            throw new \InvalidArgumentException(
                'Customer, order and transaction are required.'
            );
        }

        $amount = round($amount, 4);
        $reference = trim($reference);

        if ($amount <= 0 || $reference === '') {
            throw new \InvalidArgumentException(
                'A valid amount and reference are required.'
            );
        }

        $this->db->query("START TRANSACTION");

        try {
            $transaction = $this->db->query(
                "SELECT transaction_id,
                        wallet_id,
                        customer_id,
                        type,
                        direction,
                        amount,
                        order_id,
                        reference
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE transaction_id = '" . (int)$transaction_id . "'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$transaction->num_rows) {
                throw new \RuntimeException(
                    'Wallet checkout transaction was not found.'
                );
            }

            $row = $transaction->row;

            if ((int)$row['customer_id'] !== $customer_id) {
                throw new \RuntimeException(
                    'Wallet transaction customer mismatch.'
                );
            }

            if ($row['type'] !== 'checkout_wallet' ||
                $row['direction'] !== 'debit') {
                throw new \RuntimeException(
                    'Invalid wallet checkout transaction.'
                );
            }

            if (round((float)$row['amount'], 4) !== $amount) {
                throw new \RuntimeException(
                    'Wallet checkout amount mismatch.'
                );
            }

            if ((string)$row['reference'] !== $reference) {
                throw new \RuntimeException(
                    'Wallet checkout reference mismatch.'
                );
            }

            if ((int)$row['order_id'] > 0 &&
                (int)$row['order_id'] !== $order_id) {
                throw new \RuntimeException(
                    'Wallet transaction is already attached to another order.'
                );
            }

            if ((int)$row['order_id'] === $order_id) {
                $this->db->query("COMMIT");

                return [
                    'success' => true,
                    'already_finalized' => true,
                    'transaction_id' => $transaction_id,
                    'order_id' => $order_id,
                    'amount' => $amount
                ];
            }

            /*
             * Attach the wallet transaction to the created order.
             */
            $this->db->query(
                "UPDATE `" . DB_PREFIX . "wallet_transaction`
                 SET order_id = '" . (int)$order_id . "'
                 WHERE transaction_id = '" . (int)$transaction_id . "'"
            );

            /*
             * Keep wallet_order as the financial/order mapping record.
             * status=1 means the checkout wallet payment is finalized.
             */
            $existing = $this->db->query(
                "SELECT wallet_order_id,
                        transaction_id
                 FROM `" . DB_PREFIX . "wallet_order`
                 WHERE order_id = '" . (int)$order_id . "'
                 AND transaction_id = '" . (int)$transaction_id . "'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$existing->num_rows) {
                $this->db->query(
                    "INSERT INTO `" . DB_PREFIX . "wallet_order`
                     SET order_id = '" . (int)$order_id . "',
                         customer_id = '" . (int)$customer_id . "',
                         amount = '" . (float)$amount . "',
                         transaction_id = '" . (int)$transaction_id . "',
                         status = '1',
                         reference = '" . $this->db->escape($reference) . "',
                         date_added = NOW()"
                );
            }

            $this->db->query("COMMIT");

            return [
                'success' => true,
                'already_finalized' => false,
                'transaction_id' => $transaction_id,
                'order_id' => $order_id,
                'amount' => $amount
            ];
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }

    public function restoreReservation(
        int $customer_id,
        int $transaction_id,
        string $reference,
        int $order_id = 0
    ): array {
        if ($customer_id <= 0 || $transaction_id <= 0) {
            throw new \InvalidArgumentException(
                'Customer and transaction are required.'
            );
        }

        $reference = trim($reference);

        if ($reference === '') {
            throw new \InvalidArgumentException(
                'Wallet reservation reference is required.'
            );
        }

        $this->db->query("START TRANSACTION");

        try {
            $transaction = $this->db->query(
                "SELECT transaction_id,
                        wallet_id,
                        customer_id,
                        amount,
                        order_id,
                        reference
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE transaction_id = '" . (int)$transaction_id . "'
                 AND type = 'checkout_wallet'
                 AND direction = 'debit'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$transaction->num_rows) {
                throw new \RuntimeException(
                    'Wallet reservation was not found.'
                );
            }

            $row = $transaction->row;

            if ((int)$row['customer_id'] !== $customer_id) {
                throw new \RuntimeException(
                    'Wallet reservation customer mismatch.'
                );
            }

            if ((string)$row['reference'] !== $reference) {
                throw new \RuntimeException(
                    'Wallet reservation reference mismatch.'
                );
            }

            if ((int)$row['order_id'] > 0) {
                throw new \RuntimeException(
                    'A finalized order cannot be restored as a reservation.'
                );
            }

            $restore_reference = $reference . '-RESTORE';

            $existing_restore = $this->db->query(
                "SELECT transaction_id
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE customer_id = '" . (int)$customer_id . "'
                 AND type = 'checkout_wallet_restore'
                 AND reference = '" . $this->db->escape($restore_reference) . "'
                 LIMIT 1"
            );

            if ($existing_restore->num_rows) {
                $this->db->query("COMMIT");

                return [
                    'success' => true,
                    'already_restored' => true,
                    'transaction_id' => (int)$existing_restore->row['transaction_id']
                ];
            }

            $wallet = $this->db->query(
                "SELECT wallet_id,
                        balance
                 FROM `" . DB_PREFIX . "wallet`
                 WHERE wallet_id = '" . (int)$row['wallet_id'] . "'
                 AND customer_id = '" . (int)$customer_id . "'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$wallet->num_rows) {
                throw new \RuntimeException(
                    'Wallet was not found.'
                );
            }

            $before = round((float)$wallet->row['balance'], 4);
            $amount = round((float)$row['amount'], 4);
            $after = round($before + $amount, 4);

            $this->db->query(
                "UPDATE `" . DB_PREFIX . "wallet`
                 SET balance = '" . (float)$after . "',
                     date_modified = NOW()
                 WHERE wallet_id = '" . (int)$wallet->row['wallet_id'] . "'"
            );

            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "wallet_transaction`
                 SET wallet_id = '" . (int)$wallet->row['wallet_id'] . "',
                     customer_id = '" . (int)$customer_id . "',
                     type = 'checkout_wallet_restore',
                     direction = 'credit',
                     amount = '" . (float)$amount . "',
                     balance_before = '" . (float)$before . "',
                     balance_after = '" . (float)$after . "',
                     reference = '" . $this->db->escape($restore_reference) . "',
                     order_id = '" . (int)$order_id . "',
                     admin_user_id = '0',
                     date_added = NOW()"
            );

            $restore_transaction_id = (int)$this->db->getLastId();

            $this->db->query("COMMIT");

            return [
                'success' => true,
                'already_restored' => false,
                'transaction_id' => $restore_transaction_id,
                'amount' => $amount
            ];
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
}
