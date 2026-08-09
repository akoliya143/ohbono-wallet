<?php
/**
 * OHBONO Wallet Service
 *
 * Central financial mutation service.
 *
 * All credits/debits use a locked wallet row and create an immutable
 * transaction record containing before/after balances.
 */
class OhbonoWalletService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function credit(
        int $customer_id,
        float $amount,
        string $type,
        string $reference,
        string $reason = '',
        int $order_id = 0,
        int $admin_user_id = 0
    ): int {
        return $this->mutate(
            $customer_id,
            abs($amount),
            $type,
            'credit',
            $reference,
            $reason,
            $order_id,
            $admin_user_id
        );
    }

    public function debit(
        int $customer_id,
        float $amount,
        string $type,
        string $reference,
        string $reason = '',
        int $order_id = 0,
        int $admin_user_id = 0
    ): int {
        return $this->mutate(
            $customer_id,
            abs($amount),
            $type,
            'debit',
            $reference,
            $reason,
            $order_id,
            $admin_user_id
        );
    }

    private function mutate(
        int $customer_id,
        float $amount,
        string $type,
        string $direction,
        string $reference,
        string $reason,
        int $order_id,
        int $admin_user_id
    ): int {
        $amount = round($amount, 4);
        $reference = trim($reference);

        if ($customer_id <= 0 ||
            $amount <= 0 ||
            $type === '' ||
            $reference === '') {
            throw new \InvalidArgumentException(
                'Invalid wallet mutation.'
            );
        }

        $this->db->query("START TRANSACTION");

        try {
            $existing = $this->db->query(
                "SELECT transaction_id
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE customer_id = '" . (int)$customer_id . "'
                 AND reference = '" . $this->db->escape($reference) . "'
                 LIMIT 1
                 FOR UPDATE"
            );

            if ($existing->num_rows) {
                $this->db->query("COMMIT");

                return (int)$existing->row['transaction_id'];
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

            $before = round((float)$wallet->row['balance'], 4);

            if ($direction === 'debit') {
                if ($amount > $before) {
                    throw new \RuntimeException(
                        'Insufficient wallet balance.'
                    );
                }

                $after = round($before - $amount, 4);
            } else {
                $after = round($before + $amount, 4);
            }

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
                     type = '" . $this->db->escape($type) . "',
                     direction = '" . $this->db->escape($direction) . "',
                     amount = '" . (float)$amount . "',
                     balance_before = '" . (float)$before . "',
                     balance_after = '" . (float)$after . "',
                     reference = '" . $this->db->escape($reference) . "',
                     order_id = '" . (int)$order_id . "',
                     admin_user_id = '" . (int)$admin_user_id . "',
                     date_added = NOW()"
            );

            $transaction_id = (int)$this->db->getLastId();

            $this->db->query("COMMIT");

            return $transaction_id;
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
}
