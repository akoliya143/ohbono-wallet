<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

class WalletNotifications extends \Opencart\System\Engine\Model {
    public function getNotifications(int $customer_id, int $start = 0, int $limit = 20): array {
        if ($customer_id <= 0) return [];
        $start = max(0, $start); $limit = max(1, min(50, $limit));
        return $this->db->query("SELECT wn.notification_id, wn.transaction_id, wn.message, wn.is_read, wn.date_added, wt.type, wt.direction
            FROM `" . DB_PREFIX . "wallet_notification` wn
            INNER JOIN `" . DB_PREFIX . "wallet_transaction` wt ON wt.transaction_id = wn.transaction_id
            WHERE wn.customer_id = '" . (int)$customer_id . "'
            ORDER BY wn.notification_id DESC LIMIT " . $start . ", " . $limit)->rows;
    }

    public function getUnreadCount(int $customer_id): int {
        if ($customer_id <= 0) return 0;
        return (int)$this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "wallet_notification`
            WHERE customer_id = '" . (int)$customer_id . "' AND is_read = '0'")->row['total'];
    }

    public function markRead(int $customer_id, int $notification_id): bool {
        if ($customer_id <= 0 || $notification_id <= 0) return false;
        $this->db->query("UPDATE `" . DB_PREFIX . "wallet_notification`
            SET is_read = '1', date_read = NOW()
            WHERE notification_id = '" . (int)$notification_id . "'
            AND customer_id = '" . (int)$customer_id . "'");
        return $this->db->countAffected() > 0;
    }
}
