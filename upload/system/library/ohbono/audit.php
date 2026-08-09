<?php
/**
 * OHBONO Wallet immutable audit logger.
 *
 * Audit records are append-only from the extension layer. No update/delete
 * method is exposed here.
 */
class OhbonoWalletAudit
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function log(
        int $customer_id,
        string $action,
        float $amount,
        float $balance_before,
        float $balance_after,
        string $reason,
        int $admin_user_id = 0,
        string $reference = '',
        int $transaction_id = 0
    ): int {
        $ip = isset($_SERVER['REMOTE_ADDR'])
            ? substr((string)$_SERVER['REMOTE_ADDR'], 0, 45)
            : '';

        $user_agent = isset($_SERVER['HTTP_USER_AGENT'])
            ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 500)
            : '';

        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_audit`
             SET customer_id = '" . (int)$customer_id . "',
                 transaction_id = '" . (int)$transaction_id . "',
                 admin_user_id = '" . (int)$admin_user_id . "',
                 action = '" . $this->db->escape($action) . "',
                 amount = '" . (float)$amount . "',
                 balance_before = '" . (float)$balance_before . "',
                 balance_after = '" . (float)$balance_after . "',
                 reference = '" . $this->db->escape($reference) . "',
                 reason = '" . $this->db->escape($reason) . "',
                 ip_address = '" . $this->db->escape($ip) . "',
                 user_agent = '" . $this->db->escape($user_agent) . "',
                 date_added = NOW()"
        );

        return (int)$this->db->getLastId();
    }
}
