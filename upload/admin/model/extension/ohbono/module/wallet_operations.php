<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletOperations extends \Opencart\System\Engine\Model
{
    public function getRecent(
        int $start = 0,
        int $limit = 100
    ): array {
        $start = max(0, $start);
        $limit = max(1, min(200, $limit));

        return $this->db->query(
            "SELECT log_id,
                    event,
                    customer_id,
                    order_id,
                    transaction_id,
                    status,
                    message,
                    date_added
             FROM `" . DB_PREFIX . "wallet_operation_log`
             ORDER BY log_id DESC
             LIMIT " . $start . ", " . $limit
        )->rows;
    }
}
