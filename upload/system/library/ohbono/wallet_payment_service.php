<?php
/**
 * OHBONO Wallet Payment Service
 *
 * Final server-side wallet authorization and deduction.
 *
 * Important:
 * - Never trusts a browser-supplied wallet balance.
 * - Revalidates the order total.
 * - Locks the wallet row before changing the balance.
 * - Uses an idempotent payment reference.
 * - Records the wallet transaction atomically.
 */
class OhbonoWalletPaymentService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function authorizeAndCapture(
        int $customer_id,
        float $order_total,
        float $requested_amount,
        string $reference,
        int $order_id = 0
    ): array {
        $order_total = round(
            max(0.0, $order_total),
            4
        );

        $requested_amount = round(
            max(0.0, $requested_amount),
            4
        );

        $reference = trim($reference);

        if ($customer_id <= 0 ||
            $order_total <= 0 ||
            $requested_amount <= 0 ||
            $reference === '') {
            throw new \InvalidArgumentException(
                'Invalid wallet payment request.'
            );
        }

        if ($requested_amount > $order_total) {
            throw new \RuntimeException(
                'Wallet amount cannot exceed order total.'
            );
        }

        $this->db->query("START TRANSACTION");

        try {
            /*
             * Idempotency check.
             *
             * A repeated callback/request with the same reference must return
             * the existing transaction instead of charging twice.
             */
            $existing = $this->db->query(
                "SELECT transaction_id,
                        amount,
                        balance_after
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE customer_id = '" .
                    (int)$customer_id . "'
                 AND reference = '" .
                    $this->db->escape($reference) . "'
                 AND type = 'wallet_payment'
                 LIMIT 1
                 FOR UPDATE"
            );

            if ($existing->num_rows) {
                $this->db->query("COMMIT");

                return [
                    'transaction_id' =>
                        (int)$existing->row['transaction_id'],
                    'amount' =>
                        (float)$existing->row['amount'],
                    'balance_after' =>
                        (float)$existing->row['balance_after'],
                    'idempotent' => true
                ];
            }

            /*
             * Lock the wallet. The balance used for authorization comes from
             * the database, never from the browser.
             */
            $wallet = $this->db->query(
                "SELECT wallet_id,
                        balance,
                        status
                 FROM `" . DB_PREFIX . "wallet`
                 WHERE customer_id = '" .
                    (int)$customer_id . "'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$wallet->num_rows ||
                !(int)$wallet->row['status']) {
                throw new \RuntimeException(
                    'Active wallet not found.'
                );
            }

            $before = round(
                (float)$wallet->row['balance'],
                4
            );

            if ($requested_amount > $before) {
                throw new \RuntimeException(
                    'Insufficient wallet balance.'
                );
            }

            $after = round(
                $before - $requested_amount,
                4
            );

            $this->db->query(
                "UPDATE `" . DB_PREFIX . "wallet`
                 SET balance = '" .
                    (float)$after . "',
                     date_modified = NOW()
                 WHERE wallet_id = '" .
                    (int)$wallet->row['wallet_id'] . "'"
            );

            $this->db->query(
                "INSERT INTO `" .
                DB_PREFIX . "wallet_transaction`
                 SET wallet_id = '" .
                    (int)$wallet->row['wallet_id'] . "',
                     customer_id = '" .
                    (int)$customer_id . "',
                     type = 'wallet_payment',
                     direction = 'debit',
                     amount = '" .
                    (float)$requested_amount . "',
                     balance_before = '" .
                    (float)$before . "',
                     balance_after = '" .
                    (float)$after . "',
                     reference = '" .
                    $this->db->escape($reference) . "',
                     order_id = '" .
                    (int)$order_id . "',
                     admin_user_id = '0',
                     date_added = NOW()"
            );

            $transaction_id =
                (int)$this->db->getLastId();

            $this->db->query("COMMIT");

            return [
                'transaction_id' => $transaction_id,
                'amount' => $requested_amount,
                'balance_after' => $after,
                'idempotent' => false
            ];
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
}
