<?php
/**
 * OHBONO Wallet Service
 *
 * Central financial ledger service.
 *
 * Rules:
 * - Never trust a wallet amount from the browser.
 * - Every debit is revalidated against the current database balance.
 * - A wallet transaction is immutable.
 * - Order wallet debits are idempotent.
 * - All balance changes happen inside a database transaction.
 */

class OhbonoWalletService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getWallet(int $customer_id, bool $for_update = false): array
    {
        if ($customer_id <= 0) {
            return [];
        }

        $sql = "SELECT *
                FROM `" . DB_PREFIX . "wallet`
                WHERE customer_id = '" . (int)$customer_id . "'
                LIMIT 1";

        if ($for_update) {
            $sql .= " FOR UPDATE";
        }

        $query = $this->db->query($sql);

        return $query->num_rows ? $query->row : [];
    }

    public function getBalance(int $customer_id): float
    {
        $wallet = $this->getWallet($customer_id);

        return $wallet ? round((float)$wallet['balance'], 4) : 0.0;
    }

    public function ensureWallet(int $customer_id): int
    {
        if ($customer_id <= 0) {
            throw new InvalidArgumentException('Invalid customer ID.');
        }

        $wallet = $this->getWallet($customer_id);

        if ($wallet) {
            return (int)$wallet['wallet_id'];
        }

        $now = date('Y-m-d H:i:s');

        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet`
             SET customer_id = '" . (int)$customer_id . "',
                 balance = '0.0000',
                 status = '1',
                 date_added = '" . $this->db->escape($now) . "',
                 date_modified = '" . $this->db->escape($now) . "'"
        );

        return (int)$this->db->getLastId();
    }

    public function credit(
        int $customer_id,
        float $amount,
        string $type = 'admin_credit',
        string $reference = '',
        string $comment = '',
        int $order_id = 0,
        int $admin_user_id = 0
    ): int {
        $amount = round($amount, 4);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be greater than zero.');
        }

        $this->begin();

        try {
            $wallet_id = $this->ensureWallet($customer_id);
            $wallet = $this->getWallet($customer_id, true);

            if (!$wallet) {
                throw new RuntimeException('Wallet could not be created.');
            }

            $before = round((float)$wallet['balance'], 4);
            $after = round($before + $amount, 4);

            $transaction_id = $this->insertTransaction(
                $wallet_id,
                $customer_id,
                $type,
                'credit',
                $amount,
                $before,
                $after,
                $reference,
                $comment,
                $order_id,
                $admin_user_id
            );

            $this->updateBalance($wallet_id, $after);

            $this->commit();

            return $transaction_id;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function debit(
        int $customer_id,
        float $amount,
        string $type = 'order_payment',
        string $reference = '',
        string $comment = '',
        int $order_id = 0,
        int $admin_user_id = 0
    ): int {
        $amount = round($amount, 4);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Debit amount must be greater than zero.');
        }

        $this->begin();

        try {
            $wallet_id = $this->ensureWallet($customer_id);
            $wallet = $this->getWallet($customer_id, true);

            if (!$wallet) {
                throw new RuntimeException('Wallet could not be loaded.');
            }

            $before = round((float)$wallet['balance'], 4);

            if ($before < $amount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $after = round($before - $amount, 4);

            $transaction_id = $this->insertTransaction(
                $wallet_id,
                $customer_id,
                $type,
                'debit',
                $amount,
                $before,
                $after,
                $reference,
                $comment,
                $order_id,
                $admin_user_id
            );

            $this->updateBalance($wallet_id, $after);

            $this->commit();

            return $transaction_id;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function debitForOrder(
        int $customer_id,
        int $order_id,
        float $amount
    ): int {
        if ($order_id <= 0) {
            throw new InvalidArgumentException('Invalid order ID.');
        }

        $amount = round($amount, 4);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Order wallet amount must be greater than zero.');
        }

        $this->begin();

        try {
            $existing = $this->db->query(
                "SELECT transaction_id
                 FROM `" . DB_PREFIX . "wallet_order`
                 WHERE order_id = '" . (int)$order_id . "'
                 AND status = '1'
                 LIMIT 1
                 FOR UPDATE"
            );

            if ($existing->num_rows) {
                $transaction_id = (int)$existing->row['transaction_id'];

                $this->commit();

                return $transaction_id;
            }

            $wallet_id = $this->ensureWallet($customer_id);
            $wallet = $this->getWallet($customer_id, true);

            if (!$wallet) {
                throw new RuntimeException('Wallet could not be loaded.');
            }

            $before = round((float)$wallet['balance'], 4);

            if ($before < $amount) {
                throw new RuntimeException('Insufficient wallet balance at final checkout validation.');
            }

            $after = round($before - $amount, 4);

            $transaction_id = $this->insertTransaction(
                $wallet_id,
                $customer_id,
                'order_payment',
                'debit',
                $amount,
                $before,
                $after,
                'ORDER-' . $order_id,
                'Wallet payment for order #' . $order_id,
                $order_id,
                0
            );

            $this->updateBalance($wallet_id, $after);

            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "wallet_order`
                 SET order_id = '" . (int)$order_id . "',
                     customer_id = '" . (int)$customer_id . "',
                     amount = '" . (float)$amount . "',
                     transaction_id = '" . (int)$transaction_id . "',
                     status = '1',
                     date_added = NOW()"
            );

            $this->commit();

            return $transaction_id;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function hasOrderDebit(int $order_id): bool
    {
        if ($order_id <= 0) {
            return false;
        }

        $query = $this->db->query(
            "SELECT wallet_order_id
             FROM `" . DB_PREFIX . "wallet_order`
             WHERE order_id = '" . (int)$order_id . "'
             AND status = '1'
             LIMIT 1"
        );

        return (bool)$query->num_rows;
    }

    private function insertTransaction(
        int $wallet_id,
        int $customer_id,
        string $type,
        string $direction,
        float $amount,
        float $before,
        float $after,
        string $reference,
        string $comment,
        int $order_id,
        int $admin_user_id
    ): int {
        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_transaction`
             SET wallet_id = '" . (int)$wallet_id . "',
                 customer_id = '" . (int)$customer_id . "',
                 type = '" . $this->db->escape($type) . "',
                 direction = '" . $this->db->escape($direction) . "',
                 amount = '" . (float)$amount . "',
                 balance_before = '" . (float)$before . "',
                 balance_after = '" . (float)$after . "',
                 reference = '" . $this->db->escape($reference) . "',
                 comment = '" . $this->db->escape($comment) . "',
                 order_id = '" . (int)$order_id . "',
                 admin_user_id = '" . (int)$admin_user_id . "',
                 date_added = NOW()"
        );

        return (int)$this->db->getLastId();
    }

    private function updateBalance(int $wallet_id, float $balance): void
    {
        $this->db->query(
            "UPDATE `" . DB_PREFIX . "wallet`
             SET balance = '" . (float)$balance . "',
                 date_modified = NOW()
             WHERE wallet_id = '" . (int)$wallet_id . "'"
        );
    }

    private function begin(): void
    {
        $this->db->query("START TRANSACTION");
    }

    private function commit(): void
    {
        $this->db->query("COMMIT");
    }

    private function rollback(): void
    {
        $this->db->query("ROLLBACK");
    }
}
