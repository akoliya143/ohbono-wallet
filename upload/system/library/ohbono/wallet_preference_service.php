<?php
/**
 * OHBONO Wallet notification preferences.
 *
 * Preferences are customer-owned and only affect notification delivery.
 * They do not affect wallet balance or transaction creation.
 */
class OhbonoWalletPreferenceService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function get(int $customer_id): array
    {
        $defaults = [
            'email_enabled' => 1,
            'email_credit' => 1,
            'email_debit' => 1,
            'email_refund' => 1
        ];

        if ($customer_id <= 0) {
            return $defaults;
        }

        $query = $this->db->query(
            "SELECT email_enabled,
                    email_credit,
                    email_debit,
                    email_refund
             FROM `" . DB_PREFIX . "wallet_notification_preference`
             WHERE customer_id = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        if (!$query->num_rows) {
            return $defaults;
        }

        return [
            'email_enabled' => (int)$query->row['email_enabled'],
            'email_credit' => (int)$query->row['email_credit'],
            'email_debit' => (int)$query->row['email_debit'],
            'email_refund' => (int)$query->row['email_refund']
        ];
    }

    public function save(int $customer_id, array $data): bool
    {
        if ($customer_id <= 0) {
            return false;
        }

        $current = $this->get($customer_id);

        $email_enabled = isset($data['email_enabled'])
            ? (int)(bool)$data['email_enabled']
            : $current['email_enabled'];

        $email_credit = isset($data['email_credit'])
            ? (int)(bool)$data['email_credit']
            : $current['email_credit'];

        $email_debit = isset($data['email_debit'])
            ? (int)(bool)$data['email_debit']
            : $current['email_debit'];

        $email_refund = isset($data['email_refund'])
            ? (int)(bool)$data['email_refund']
            : $current['email_refund'];

        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_notification_preference`
             SET customer_id = '" . (int)$customer_id . "',
                 email_enabled = '" . $email_enabled . "',
                 email_credit = '" . $email_credit . "',
                 email_debit = '" . $email_debit . "',
                 email_refund = '" . $email_refund . "'
             ON DUPLICATE KEY UPDATE
                 email_enabled = VALUES(email_enabled),
                 email_credit = VALUES(email_credit),
                 email_debit = VALUES(email_debit),
                 email_refund = VALUES(email_refund)"
        );

        return true;
    }
}
