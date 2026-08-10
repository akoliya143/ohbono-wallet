<?php
/**
 * OHBONO Wallet Reversal Service
 *
 * Reverses a previous wallet debit by creating a compensating credit.
 *
 * The original transaction is never edited or deleted. A separate reversal
 * transaction is created and linked to the original transaction through the
 * reference and reason fields.
 */
class OhbonoWalletReversalService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function reverse(
        int $customer_id,
        int $original_transaction_id,
        string $reference,
        string $reason,
        int $admin_user_id = 0
    ): int {
        if ($customer_id <= 0 ||
            $original_transaction_id <= 0 ||
            trim($reference) === '' ||
            trim($reason) === '') {
            throw new \InvalidArgumentException(
                'Customer, transaction, reference and reason are required.'
            );
        }

        $this->db->query("START TRANSACTION");

        try {
            $original = $this->db->query(
                "SELECT transaction_id,
                        wallet_id,
                        customer_id,
                        type,
                        direction,
                        amount,
                        balance_after,
                        order_id,
                        reference
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE transaction_id = '" .
                    (int)$original_transaction_id . "'
                 AND customer_id = '" .
                    (int)$customer_id . "'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$original->num_rows) {
                throw new \RuntimeException(
                    'Original wallet transaction not found.'
                );
            }

            $row = $original->row;

            if ($row['direction'] !== 'debit') {
                throw new \RuntimeException(
                    'Only wallet debits can be reversed.'
                );
            }

            $existing = $this->db->query(
                "SELECT transaction_id
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE customer_id = '" .
                    (int)$customer_id . "'
                 AND reference = '" .
                    $this->db->escape(trim($reference)) . "'
                 AND type = 'wallet_reversal'
                 LIMIT 1
                 FOR UPDATE"
            );

            if ($existing->num_rows) {
                $this->db->query("COMMIT");

                return (int)$existing->row['transaction_id'];
            }

            $wallet = $this->db->query(
                "SELECT wallet_id,
                        balance,
                        status
                 FROM `" . DB_PREFIX . "wallet`
                 WHERE wallet_id = '" .
                    (int)$row['wallet_id'] . "'
                 AND customer_id = '" .
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

            $amount = round(
                abs((float)$row['amount']),
                4
            );

            if ($amount <= 0) {
                throw new \RuntimeException(
                    'Original transaction has no reversible amount.'
                );
            }

            $after = round(
                $before + $amount,
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
                     type = 'wallet_reversal',
                     direction = 'credit',
                     amount = '" .
                    (float)$amount . "',
                     balance_before = '" .
                    (float)$before . "',
                     balance_after = '" .
                    (float)$after . "',
                     reference = '" .
                    $this->db->escape(trim($reference)) . "',
                     order_id = '" .
                    (int)$row['order_id'] . "',
                     admin_user_id = '" .
                    (int)$admin_user_id . "',
                     date_added = NOW()"
            );

            $reversal_id =
                (int)$this->db->getLastId();

            $this->db->query(
                "INSERT INTO `" .
                DB_PREFIX . "wallet_admin_audit`
                 SET admin_user_id = '" .
                    (int)$admin_user_id . "',
                     customer_id = '" .
                    (int)$customer_id . "',
                     transaction_id = '" .
                    (int)$reversal_id . "',
                     action = 'wallet_reversal',
                     reason = '" .
                    $this->db->escape(
                        $reason .
                        ' Original transaction #' .
                        (int)$original_transaction_id
                    ) . "',
                     date_added = NOW()"
            );

            $this->db->query("COMMIT");

            return $reversal_id;
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
}
