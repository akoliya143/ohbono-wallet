<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletReconciliation extends \Opencart\System\Engine\Model
{
    public function getOrdersRequiringReconciliation(
        int $start = 0,
        int $limit = 100
    ): array {
        $start = max(0, $start);
        $limit = max(1, min(200, $limit));

        return $this->db->query(
            "SELECT
                o.order_id,
                o.customer_id,
                o.total,
                o.order_status_id,
                wt.transaction_id,
                wt.amount AS wallet_amount,
                wt.date_added AS wallet_date,
                wps.state AS wallet_state,
                wps.remaining_amount
             FROM `" . DB_PREFIX . "order` o
             INNER JOIN `" . DB_PREFIX . "wallet_transaction` wt
                ON wt.order_id = o.order_id
                AND wt.type = 'wallet_payment'
                AND wt.direction = 'debit'
             LEFT JOIN `" . DB_PREFIX . "wallet_transaction` wr
                ON wr.order_id = o.order_id
                AND wr.type = 'wallet_reversal'
                AND wr.direction = 'credit'
             LEFT JOIN `" . DB_PREFIX . "wallet_payment_state` wps
                ON wps.order_id = o.order_id
             WHERE wr.transaction_id IS NULL
             AND (
                wps.state = 'reconciliation_required'
                OR (
                    wps.wallet_payment_state_id IS NULL
                    AND o.order_status_id = '0'
                )
             )
             ORDER BY wt.transaction_id ASC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }
}
