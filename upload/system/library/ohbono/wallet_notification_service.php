<?php
class OhbonoWalletNotificationService {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function createForTransaction(int $transaction_id, string $message): int {
        $message = trim($message);
        if ($transaction_id <= 0 || $message === '') throw new \InvalidArgumentException('Transaction and message are required.');

        $tx = $this->db->query("SELECT transaction_id, customer_id FROM `" . DB_PREFIX . "wallet_transaction`
            WHERE transaction_id = '" . (int)$transaction_id . "' LIMIT 1");
        if (!$tx->num_rows) throw new \RuntimeException('Wallet transaction not found.');

        $existing = $this->db->query("SELECT notification_id FROM `" . DB_PREFIX . "wallet_notification`
            WHERE transaction_id = '" . (int)$transaction_id . "' LIMIT 1");
        if ($existing->num_rows) return (int)$existing->row['notification_id'];

        $this->db->query("INSERT INTO `" . DB_PREFIX . "wallet_notification`
            SET customer_id = '" . (int)$tx->row['customer_id'] . "',
                transaction_id = '" . (int)$transaction_id . "',
                message = '" . $this->db->escape($message) . "',
                is_read = '0', date_added = NOW()");
        return (int)$this->db->getLastId();
    }
}
