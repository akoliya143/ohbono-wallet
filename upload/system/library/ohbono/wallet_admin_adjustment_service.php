<?php
/**
 * OHBONO Wallet Admin Adjustment Service
 *
 * Controlled manual wallet credit/debit operations.
 * Every mutation requires an authenticated admin, a reason and a unique
 * idempotency reference.
 */
class OhbonoWalletAdminAdjustmentService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function adjust(
        int $admin_user_id,
        int $customer_id,
        float $amount,
        string $direction,
        string $reason,
        string $reference
    ): int {
        $amount = round(abs($amount), 4);
        $direction = trim($direction);
        $reason = trim($reason);
        $reference = trim($reference);

        if ($admin_user_id <= 0 ||
            $customer_id <= 0 ||
            $amount <= 0 ||
            !in_array($direction, ['credit', 'debit'], true) ||
            $reason === '' ||
            $reference === '') {
            throw new \InvalidArgumentException(
                'Admin, customer, amount, direction, reason and reference are required.'
            );
        }

        $this->db->query("START TRANSACTION");

        try {
            $existing = $this->db->query(
                "SELECT transaction_id
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE customer_id = '" . (int)$customer_id . "'
                 AND reference = '" .
                    $this->db->escape($reference) . "'
                 LIMIT 1
                 FOR UPDATE"
            );

            if ($existing->num_rows) {
                $transaction_id =
                    (int)$existing->row['transaction_id'];

                $this->db->query("COMMIT");

                return $transaction_id;
            }

            $wallet = $this->db->query(
                "SELECT wallet_id, balance
                 FROM `" . DB_PREFIX . "wallet`
                 WHERE customer_id = '" . (int)$customer_id . "'
                 AND status = '1'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$wallet->num_rows) {
                throw new \RuntimeException(
                    'Active wallet not found.'
                );
            }

            $before = round(
                (float)$wallet->row['balance'],
                4
            );

            if ($direction === 'debit') {
                if ($amount > $before) {
                    throw new \RuntimeException(
                        'Insufficient wallet balance.'
                    );
                }

                $after = round(
                    $before - $amount,
                    4
                );
            } else {
                $after = round(
                    $before + $amount,
                    4
                );
            }

            $this->db->query(
                "UPDATE `" . DB_PREFIX . "wallet`
                 SET balance = '" . (float)$after . "',
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
                     type = 'admin_adjustment',
                     direction = '" .
                    $this->db->escape($direction) . "',
                     amount = '" . (float)$amount . "',
                     balance_before = '" .
                    (float)$before . "',
                     balance_after = '" .
                    (float)$after . "',
                     reference = '" .
                    $this->db->escape($reference) . "',
                     order_id = '0',
                     admin_user_id = '" .
                    (int)$admin_user_id . "',
                     date_added = NOW()"
            );

            $transaction_id =
                (int)$this->db->getLastId();

            $this->db->query(
                "INSERT INTO `" .
                DB_PREFIX . "wallet_admin_audit`
                 SET admin_user_id = '" .
                    (int)$admin_user_id . "',
                     customer_id = '" .
                    (int)$customer_id . "',
                     transaction_id = '" .
                    (int)$transaction_id . "',
                     action = '" .
                    $this->db->escape(
                        'admin_wallet_' . $direction
                    ) . "',
                     reason = '" .
                    $this->db->escape($reason) . "',
                     date_added = NOW()"
            );

            $this->db->query("COMMIT");

            return $transaction_id;
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
}
