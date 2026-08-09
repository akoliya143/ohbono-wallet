<?php
/**
 * OHBONO Wallet migration helper.
 *
 * Provides a small version registry so future commits can add migrations
 * without recreating or destroying existing wallet data.
 */
class OhbonoWalletMigration
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getVersion(): int
    {
        $query = $this->db->query(
            "SELECT `value`
             FROM `" . DB_PREFIX . "setting`
             WHERE `code` = 'ohbono_wallet'
             AND `key` = 'ohbono_wallet_version'
             AND store_id = '0'
             LIMIT 1"
        );

        return $query->num_rows
            ? (int)$query->row['value']
            : 0;
    }

    public function setVersion(int $version): void
    {
        $version = max(0, $version);

        $query = $this->db->query(
            "SELECT setting_id
             FROM `" . DB_PREFIX . "setting`
             WHERE `code` = 'ohbono_wallet'
             AND `key` = 'ohbono_wallet_version'
             AND store_id = '0'
             LIMIT 1"
        );

        if ($query->num_rows) {
            $this->db->query(
                "UPDATE `" . DB_PREFIX . "setting`
                 SET `value` = '" . (int)$version . "',
                     serialized = '0'
                 WHERE setting_id = '" . (int)$query->row['setting_id'] . "'"
            );
        } else {
            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "setting`
                 SET store_id = '0',
                     `code` = 'ohbono_wallet',
                     `key` = 'ohbono_wallet_version',
                     `value` = '" . (int)$version . "',
                     serialized = '0'"
            );
        }
    }
}
