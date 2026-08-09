<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getBalance(int $customer_id): float
    {
        if ($customer_id <= 0) {
            return 0.0;
        }

        $query = $this->db->query(
            "SELECT COALESCE(balance, 0) AS balance
             FROM `" . DB_PREFIX . "wallet`
             WHERE customer_id = '" . (int)$customer_id . "'
             AND status = '1'
             LIMIT 1"
        );

        return $query->num_rows
            ? round((float)$query->row['balance'], 4)
            : 0.0;
    }

    public function getQuote(int $customer_id, float $order_total): array
    {
        if ($customer_id <= 0) {
            throw new \InvalidArgumentException('Customer is required.');
        }

        $order_total = round($order_total, 4);

        if ($order_total < 0) {
            throw new \InvalidArgumentException('Invalid order total.');
        }

        $balance = $this->getBalance($customer_id);

        $maximum = (float)$this->config->get('ohbono_wallet_maximum_use');

        if ($maximum > 0) {
            $available = min($balance, $maximum);
        } else {
            $available = $balance;
        }

        $applied = min($available, $order_total);

        return [
            'balance' => round($balance, 4),
            'maximum' => round(max(0, $maximum), 4),
            'available' => round(max(0, $available), 4),
            'applied' => round(max(0, $applied), 4),
            'remaining' => round(
                max(0, $order_total - $applied),
                4
            )
        ];
    }

    public function reserve(
        int $customer_id,
        float $amount,
        string $reference
    ): int {
        $amount = round($amount, 4);
        $reference = trim($reference);

        if ($customer_id <= 0 || $amount <= 0 || $reference === '') {
            throw new \InvalidArgumentException(
                'Customer, amount and reference are required.'
            );
        }

        /*
         * Commit 0040 intentionally performs the final balance validation
         * under a row lock. This prevents two concurrent checkout requests
         * from spending the same wallet balance.
         */
        $this->db->query("START TRANSACTION");

        try {
            $wallet = $this->db->query(
                "SELECT wallet_id, balance
                 FROM `" . DB_PREFIX . "wallet`
                 WHERE customer_id = '" . (int)$customer_id . "'
                 AND status = '1'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$wallet->num_rows) {
                throw new \RuntimeException('Wallet not found.');
            }

            $balance = round((float)$wallet->row['balance'], 4);

            if ($amount > $balance) {
                throw new \RuntimeException(
                    'Insufficient wallet balance.'
                );
            }

            $existing = $this->db->query(
                "SELECT transaction_id
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE customer_id = '" . (int)$customer_id . "'
                 AND reference = '" . $this->db->escape($reference) . "'
                 AND type = 'checkout_wallet'
                 LIMIT 1"
            );

            if ($existing->num_rows) {
                $this->db->query("COMMIT");

                return (int)$existing->row['transaction_id'];
            }

            $before = $balance;
            $after = round($before - $amount, 4);

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
                     type = 'checkout_wallet',
                     direction = 'debit',
                     amount = '" . (float)$amount . "',
                     balance_before = '" . (float)$before . "',
                     balance_after = '" . (float)$after . "',
                     reference = '" . $this->db->escape($reference) . "',
                     order_id = '0',
                     admin_user_id = '0',
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

    public function restore(
        int $customer_id,
        float $amount,
        string $reference,
        int $order_id = 0
    ): int {
        $amount = round($amount, 4);
        $reference = trim($reference);

        if ($customer_id <= 0 || $amount <= 0 || $reference === '') {
            throw new \InvalidArgumentException(
                'Customer, amount and reference are required.'
            );
        }

        $this->db->query("START TRANSACTION");

        try {
            $wallet = $this->db->query(
                "SELECT wallet_id, balance
                 FROM `" . DB_PREFIX . "wallet`
                 WHERE customer_id = '" . (int)$customer_id . "'
                 AND status = '1'
                 LIMIT 1
                 FOR UPDATE"
            );

            if (!$wallet->num_rows) {
                throw new \RuntimeException('Wallet not found.');
            }

            $existing = $this->db->query(
                "SELECT transaction_id
                 FROM `" . DB_PREFIX . "wallet_transaction`
                 WHERE customer_id = '" . (int)$customer_id . "'
                 AND reference = '" . $this->db->escape($reference) . "'
                 AND type = 'checkout_wallet_restore'
                 LIMIT 1"
            );

            if ($existing->num_rows) {
                $this->db->query("COMMIT");
                return (int)$existing->row['transaction_id'];
            }

            $before = round((float)$wallet->row['balance'], 4);
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
                     reference = '" . $this->db->escape($reference) . "',
                     order_id = '" . (int)$order_id . "',
                     admin_user_id = '0',
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
