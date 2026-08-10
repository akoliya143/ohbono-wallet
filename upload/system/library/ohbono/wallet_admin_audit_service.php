<?php
/**
 * OHBONO Wallet Admin Audit Service.
 *
 * This service is intended for future manual wallet adjustments.
 * It records the administrator and reason separately from the financial
 * transaction itself.
 */
class OhbonoWalletAdminAuditService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function record(
        int $admin_user_id,
        int $customer_id,
        int $transaction_id,
        string $action,
        string $reason
    ): int {
        if ($admin_user_id <= 0 ||
            $customer_id <= 0 ||
            $transaction_id <= 0 ||
            trim($action) === '' ||
            trim($reason) === '') {
            throw new \InvalidArgumentException(
                'Complete audit details are required.'
            );
        }

        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_admin_audit`
             SET admin_user_id = '" .
                (int)$admin_user_id . "',
                 customer_id = '" .
                (int)$customer_id . "',
                 transaction_id = '" .
                (int)$transaction_id . "',
                 action = '" .
                $this->db->escape($action) . "',
                 reason = '" .
                $this->db->escape($reason) . "',
                 date_added = NOW()"
        );

        return (int)$this->db->getLastId();
    }
}
