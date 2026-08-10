<?php
/**
 * OHBONO Wallet Atomic Capture Guard
 *
 * Provides the locking/idempotency sequence that a wallet payment service
 * should follow. It intentionally does not implement a second ledger.
 */
class OhbonoWalletAtomicCapture
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function begin(): void
    {
        $this->db->query('START TRANSACTION');
    }

    public function lockWallet(
        int $wallet_id,
        int $customer_id
    ): array {
        if ($wallet_id <= 0 || $customer_id <= 0) {
            throw new \InvalidArgumentException(
                'Wallet and customer are required.'
            );
        }

        $query = $this->db->query(
            "SELECT wallet_id,
                    customer_id,
                    balance,
                    status
             FROM `" . DB_PREFIX . "wallet`
             WHERE wallet_id = '" .
                (int)$wallet_id . "'
             AND customer_id = '" .
                (int)$customer_id . "'
             LIMIT 1
             FOR UPDATE"
        );

        if (!$query->num_rows) {
            throw new \RuntimeException(
                'Wallet not found.'
            );
        }

        if (!(int)$query->row['status']) {
            throw new \RuntimeException(
                'Wallet is inactive.'
            );
        }

        return $query->row;
    }

    public function rollback(): void
    {
        $this->db->query('ROLLBACK');
    }

    public function commit(): void
    {
        $this->db->query('COMMIT');
    }
}
