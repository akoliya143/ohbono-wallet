<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletAudit extends \Opencart\System\Engine\Model
{
    public function getSummary(): array
    {
        $summary = [
            'transaction_count' => 0,
            'credit_total' => 0.0,
            'debit_total' => 0.0,
            'admin_adjustment_count' => 0,
            'checkout_count' => 0,
            'refund_count' => 0
        ];

        $query = $this->db->query(
            "SELECT
                COUNT(*) AS transaction_count,
                COALESCE(SUM(
                    CASE
                        WHEN direction = 'credit' THEN amount
                        ELSE 0
                    END
                ), 0) AS credit_total,
                COALESCE(SUM(
                    CASE
                        WHEN direction = 'debit' THEN amount
                        ELSE 0
                    END
                ), 0) AS debit_total,
                SUM(
                    CASE
                        WHEN type = 'admin_adjustment' THEN 1
                        ELSE 0
                    END
                ) AS admin_adjustment_count,
                SUM(
                    CASE
                        WHEN type = 'checkout_wallet' THEN 1
                        ELSE 0
                    END
                ) AS checkout_count,
                SUM(
                    CASE
                        WHEN type = 'order_refund' THEN 1
                        ELSE 0
                    END
                ) AS refund_count
             FROM `" . DB_PREFIX . "wallet_transaction`"
        );

        if ($query->num_rows) {
            $summary['transaction_count'] =
                (int)$query->row['transaction_count'];

            $summary['credit_total'] =
                round((float)$query->row['credit_total'], 4);

            $summary['debit_total'] =
                round((float)$query->row['debit_total'], 4);

            $summary['admin_adjustment_count'] =
                (int)$query->row['admin_adjustment_count'];

            $summary['checkout_count'] =
                (int)$query->row['checkout_count'];

            $summary['refund_count'] =
                (int)$query->row['refund_count'];
        }

        return $summary;
    }
}
