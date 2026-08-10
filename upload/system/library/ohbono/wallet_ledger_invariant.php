<?php
/**
 * OHBONO Wallet Ledger Invariant Checker
 *
 * Read-only verification helper. It never changes wallet balances.
 */
class OhbonoWalletLedgerInvariant
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function checkWallet(
        int $wallet_id
    ): array {
        if ($wallet_id <= 0) {
            throw new \InvalidArgumentException(
                'Valid wallet ID is required.'
            );
        }

        $wallet = $this->db->query(
            "SELECT wallet_id, balance
             FROM `" . DB_PREFIX . "wallet`
             WHERE wallet_id = '" .
                (int)$wallet_id . "'
             LIMIT 1"
        );

        if (!$wallet->num_rows) {
            throw new \RuntimeException(
                'Wallet not found.'
            );
        }

        $transactions = $this->db->query(
            "SELECT
                transaction_id,
                direction,
                amount,
                balance_before,
                balance_after
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE wallet_id = '" .
                (int)$wallet_id . "'
             ORDER BY transaction_id ASC"
        )->rows;

        $previous = null;
        $errors = [];

        foreach ($transactions as $transaction) {
            $before = round(
                (float)$transaction['balance_before'],
                4
            );

            $amount = round(
                abs((float)$transaction['amount']),
                4
            );

            $after = round(
                (float)$transaction['balance_after'],
                4
            );

            if ($previous !== null &&
                abs($before - $previous) > 0.0001) {
                $errors[] =
                    'Transaction #' .
                    (int)$transaction['transaction_id'] .
                    ' has an invalid opening balance.';
            }

            $expected = $transaction['direction'] === 'debit'
                ? round($before - $amount, 4)
                : round($before + $amount, 4);

            if (abs($expected - $after) > 0.0001) {
                $errors[] =
                    'Transaction #' .
                    (int)$transaction['transaction_id'] .
                    ' has an invalid balance transition.';
            }

            $previous = $after;
        }

        $current = round(
            (float)$wallet->row['balance'],
            4
        );

        if ($previous !== null &&
            abs($current - $previous) > 0.0001) {
            $errors[] =
                'Wallet current balance does not match ledger ending balance.';
        }

        return [
            'wallet_id' => $wallet_id,
            'current_balance' => $current,
            'transaction_count' => count($transactions),
            'valid' => !$errors,
            'errors' => $errors
        ];
    }
}
