<?php
/**
 * OHBONO Wallet Integrity Service
 *
 * Read-only reconciliation checks. It never silently changes financial data.
 */
class OhbonoWalletIntegrity
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function checkCustomer(int $customer_id): array
    {
        $result = [
            'customer_id' => $customer_id,
            'wallet_exists' => false,
            'balance' => 0.0,
            'ledger_balance' => 0.0,
            'difference' => 0.0,
            'transaction_count' => 0,
            'duplicate_order_count' => 0,
            'orphan_transaction_count' => 0,
            'healthy' => true
        ];

        if ($customer_id <= 0) {
            $result['healthy'] = false;
            return $result;
        }

        $wallet = $this->db->query(
            "SELECT wallet_id, balance
             FROM `" . DB_PREFIX . "wallet`
             WHERE customer_id = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        if (!$wallet->num_rows) {
            $result['healthy'] = false;
            return $result;
        }

        $result['wallet_exists'] = true;
        $wallet_id = (int)$wallet->row['wallet_id'];
        $result['balance'] = round((float)$wallet->row['balance'], 4);

        $ledger = $this->db->query(
            "SELECT
                COALESCE(SUM(
                    CASE
                        WHEN direction = 'credit' THEN amount
                        WHEN direction = 'debit' THEN -amount
                        ELSE 0
                    END
                ), 0) AS ledger_balance,
                COUNT(*) AS transaction_count
             FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE customer_id = '" . (int)$customer_id . "'"
        );

        if ($ledger->num_rows) {
            $result['ledger_balance'] = round(
                (float)$ledger->row['ledger_balance'],
                4
            );

            $result['transaction_count'] =
                (int)$ledger->row['transaction_count'];
        }

        $result['difference'] = round(
            $result['balance'] - $result['ledger_balance'],
            4
        );

        $duplicates = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM (
                SELECT order_id
                FROM `" . DB_PREFIX . "wallet_order`
                WHERE customer_id = '" . (int)$customer_id . "'
                AND status = '1'
                AND order_id > 0
                GROUP BY order_id
                HAVING COUNT(*) > 1
             ) duplicate_orders"
        );

        $result['duplicate_order_count'] =
            (int)$duplicates->row['total'];

        $orphans = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM `" . DB_PREFIX . "wallet_transaction` wt
             LEFT JOIN `" . DB_PREFIX . "wallet` w
                ON w.wallet_id = wt.wallet_id
             WHERE wt.customer_id = '" . (int)$customer_id . "'
             AND w.wallet_id IS NULL"
        );

        $result['orphan_transaction_count'] =
            (int)$orphans->row['total'];

        $result['healthy'] =
            abs($result['difference']) < 0.0001 &&
            $result['duplicate_order_count'] === 0 &&
            $result['orphan_transaction_count'] === 0;

        return $result;
    }

    public function getOverview(): array
    {
        $overview = [
            'wallets' => 0,
            'customers_with_balance' => 0,
            'ledger_transactions' => 0,
            'balance_total' => 0.0,
            'ledger_total' => 0.0,
            'balance_difference' => 0.0,
            'duplicate_orders' => 0,
            'orphan_transactions' => 0,
            'healthy' => true
        ];

        $wallets = $this->db->query(
            "SELECT
                COUNT(*) AS wallets,
                COALESCE(SUM(balance), 0) AS balance_total,
                COALESCE(SUM(CASE WHEN balance > 0 THEN 1 ELSE 0 END), 0)
                    AS customers_with_balance
             FROM `" . DB_PREFIX . "wallet`"
        );

        if ($wallets->num_rows) {
            $overview['wallets'] = (int)$wallets->row['wallets'];
            $overview['balance_total'] = round(
                (float)$wallets->row['balance_total'],
                4
            );
            $overview['customers_with_balance'] =
                (int)$wallets->row['customers_with_balance'];
        }

        $ledger = $this->db->query(
            "SELECT
                COUNT(*) AS transactions,
                COALESCE(SUM(
                    CASE
                        WHEN direction = 'credit' THEN amount
                        WHEN direction = 'debit' THEN -amount
                        ELSE 0
                    END
                ), 0) AS ledger_total
             FROM `" . DB_PREFIX . "wallet_transaction`"
        );

        if ($ledger->num_rows) {
            $overview['ledger_transactions'] =
                (int)$ledger->row['transactions'];

            $overview['ledger_total'] = round(
                (float)$ledger->row['ledger_total'],
                4
            );
        }

        $overview['balance_difference'] = round(
            $overview['balance_total'] - $overview['ledger_total'],
            4
        );

        $duplicates = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM (
                SELECT order_id
                FROM `" . DB_PREFIX . "wallet_order`
                WHERE status = '1'
                AND order_id > 0
                GROUP BY order_id
                HAVING COUNT(*) > 1
             ) duplicate_orders"
        );

        $overview['duplicate_orders'] =
            (int)$duplicates->row['total'];

        $orphans = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM `" . DB_PREFIX . "wallet_transaction` wt
             LEFT JOIN `" . DB_PREFIX . "wallet` w
                ON w.wallet_id = wt.wallet_id
             WHERE w.wallet_id IS NULL"
        );

        $overview['orphan_transactions'] =
            (int)$orphans->row['total'];

        $overview['healthy'] =
            abs($overview['balance_difference']) < 0.0001 &&
            $overview['duplicate_orders'] === 0 &&
            $overview['orphan_transactions'] === 0;

        return $overview;
    }

    public function getUnhealthyCustomers(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));

        $rows = $this->db->query(
            "SELECT
                w.customer_id,
                w.wallet_id,
                w.balance,
                COALESCE(SUM(
                    CASE
                        WHEN wt.direction = 'credit' THEN wt.amount
                        WHEN wt.direction = 'debit' THEN -wt.amount
                        ELSE 0
                    END
                ), 0) AS ledger_balance
             FROM `" . DB_PREFIX . "wallet` w
             LEFT JOIN `" . DB_PREFIX . "wallet_transaction` wt
                ON wt.wallet_id = w.wallet_id
             GROUP BY w.wallet_id
             HAVING ABS(
                w.balance -
                COALESCE(SUM(
                    CASE
                        WHEN wt.direction = 'credit' THEN wt.amount
                        WHEN wt.direction = 'debit' THEN -wt.amount
                        ELSE 0
                    END
                ), 0)
             ) >= 0.0001
             ORDER BY ABS(
                w.balance -
                COALESCE(SUM(
                    CASE
                        WHEN wt.direction = 'credit' THEN wt.amount
                        WHEN wt.direction = 'debit' THEN -wt.amount
                        ELSE 0
                    END
                ), 0)
             ) DESC
             LIMIT " . $limit
        );

        return $rows->rows;
    }
}
