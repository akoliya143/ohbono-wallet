<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getSetting(string $key, $default = null)
    {
        $query = $this->db->query(
            "SELECT `setting_value`
             FROM `" . DB_PREFIX . "wallet_setting`
             WHERE `setting_key` = '" . $this->db->escape($key) . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row['setting_value'] : $default;
    }

    public function saveSetting(string $key, string $value): void
    {
        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_setting`
             SET `setting_key` = '" . $this->db->escape($key) . "',
                 `setting_value` = '" . $this->db->escape($value) . "',
                 `date_modified` = NOW()
             ON DUPLICATE KEY UPDATE
                 `setting_value` = VALUES(`setting_value`),
                 `date_modified` = NOW()"
        );
    }

    public function getSettings(): array
    {
        $settings = [];

        $query = $this->db->query(
            "SELECT `setting_key`, `setting_value`
             FROM `" . DB_PREFIX . "wallet_setting`
             ORDER BY `setting_key` ASC"
        );

        foreach ($query->rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }
}
