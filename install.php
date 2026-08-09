<?php
/**
 * OHBONO Wallet installer / upgrader.
 *
 * OpenCart 4 extension entry point.
 *
 * The installer is intentionally idempotent:
 * - Tables are created with IF NOT EXISTS.
 * - Missing columns/indexes are added only when required.
 * - Default settings are initialized without overwriting existing values.
 * - Events are upserted by event code.
 */

class ControllerExtensionOhbonoInstall extends Controller
{
    public function index(): void
    {
        $this->load->model('setting/setting');

        $this->createTables();
        $this->upgradeSchema();
        $this->initializeSettings();
        $this->registerEvents();
        $this->registerPermissions();

        $this->response->setOutput('OHBONO Wallet installation completed.');
    }

    private function createTables(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wallet` (
                `wallet_id` INT(11) NOT NULL AUTO_INCREMENT,
                `customer_id` INT(11) NOT NULL,
                `balance` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
                `status` TINYINT(1) NOT NULL DEFAULT '1',
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`wallet_id`),
                UNIQUE KEY `uk_wallet_customer` (`customer_id`),
                KEY `idx_wallet_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wallet_transaction` (
                `transaction_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `wallet_id` INT(11) NOT NULL,
                `customer_id` INT(11) NOT NULL,
                `type` VARCHAR(64) NOT NULL,
                `direction` ENUM('credit','debit') NOT NULL,
                `amount` DECIMAL(15,4) NOT NULL,
                `balance_before` DECIMAL(15,4) NOT NULL,
                `balance_after` DECIMAL(15,4) NOT NULL,
                `reference` VARCHAR(128) NOT NULL DEFAULT '',
                `comment` VARCHAR(500) NOT NULL DEFAULT '',
                `order_id` INT(11) NOT NULL DEFAULT '0',
                `admin_user_id` INT(11) NOT NULL DEFAULT '0',
                `date_added` DATETIME NOT NULL,
                PRIMARY KEY (`transaction_id`),
                KEY `idx_wallet_transaction_customer` (`customer_id`, `transaction_id`),
                KEY `idx_wallet_transaction_wallet` (`wallet_id`, `transaction_id`),
                KEY `idx_wallet_transaction_order` (`order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wallet_order` (
                `wallet_order_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `order_id` INT(11) NOT NULL,
                `customer_id` INT(11) NOT NULL,
                `amount` DECIMAL(15,4) NOT NULL,
                `transaction_id` BIGINT(20) NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT '1',
                `date_added` DATETIME NOT NULL,
                PRIMARY KEY (`wallet_order_id`),
                UNIQUE KEY `uk_wallet_order_order` (`order_id`),
                KEY `idx_wallet_order_customer` (`customer_id`),
                KEY `idx_wallet_order_transaction` (`transaction_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function upgradeSchema(): void
    {
        $this->ensureColumn(
            DB_PREFIX . 'wallet',
            'status',
            "TINYINT(1) NOT NULL DEFAULT '1'"
        );

        $this->ensureColumn(
            DB_PREFIX . 'wallet_transaction',
            'admin_user_id',
            "INT(11) NOT NULL DEFAULT '0'"
        );

        $this->ensureColumn(
            DB_PREFIX . 'wallet_transaction',
            'comment',
            "VARCHAR(500) NOT NULL DEFAULT ''"
        );

        $this->ensureIndex(
            DB_PREFIX . 'wallet',
            'uk_wallet_customer',
            "UNIQUE KEY `uk_wallet_customer` (`customer_id`)"
        );

        $this->ensureIndex(
            DB_PREFIX . 'wallet_order',
            'uk_wallet_order_order',
            "UNIQUE KEY `uk_wallet_order_order` (`order_id`)"
        );

        $this->ensureIndex(
            DB_PREFIX . 'wallet_transaction',
            'idx_wallet_transaction_customer',
            "KEY `idx_wallet_transaction_customer` (`customer_id`, `transaction_id`)"
        );
    }

    private function ensureColumn(
        string $table,
        string $column,
        string $definition
    ): void {
        $query = $this->db->query(
            "SHOW COLUMNS FROM `" . $this->db->escape($table) . "`
             LIKE '" . $this->db->escape($column) . "'"
        );

        if (!$query->num_rows) {
            $this->db->query(
                "ALTER TABLE `" . $this->db->escape($table) . "`
                 ADD COLUMN `" . $this->db->escape($column) . "` " . $definition
            );
        }
    }

    private function ensureIndex(
        string $table,
        string $index,
        string $definition
    ): void {
        $query = $this->db->query(
            "SHOW INDEX FROM `" . $this->db->escape($table) . "`
             WHERE Key_name = '" . $this->db->escape($index) . "'"
        );

        if (!$query->num_rows) {
            $this->db->query(
                "ALTER TABLE `" . $this->db->escape($table) . "`
                 ADD " . $definition
            );
        }
    }

    private function initializeSettings(): void
    {
        $defaults = [
            'ohbono_wallet_status' => 1,
            'ohbono_wallet_allow_checkout' => 1,
            'ohbono_wallet_minimum_use' => 0,
            'ohbono_wallet_maximum_use' => 0,
            'ohbono_wallet_history_limit' => 20,
            'ohbono_wallet_sort_order' => 100
        ];

        foreach ($defaults as $key => $value) {
            $query = $this->db->query(
                "SELECT setting_id
                 FROM `" . DB_PREFIX . "setting`
                 WHERE store_id = '0'
                 AND `code` = 'ohbono_wallet'
                 AND `key` = '" . $this->db->escape($key) . "'
                 LIMIT 1"
            );

            if (!$query->num_rows) {
                $this->db->query(
                    "INSERT INTO `" . DB_PREFIX . "setting`
                     SET store_id = '0',
                         `code` = 'ohbono_wallet',
                         `key` = '" . $this->db->escape($key) . "',
                         `value` = '" . $this->db->escape((string)$value) . "',
                         serialized = '0'"
                );
            }
        }
    }

    private function registerEvents(): void
    {
        $this->upsertEvent(
            'ohbono_wallet_account_menu',
            'OHBONO Wallet customer account navigation',
            'catalog/controller/account/account/after',
            'extension/ohbono/event/account.account',
            1,
            100
        );
    }

    private function upsertEvent(
        string $code,
        string $description,
        string $trigger,
        string $action,
        int $status,
        int $sort_order
    ): void {
        $query = $this->db->query(
            "SELECT event_id
             FROM `" . DB_PREFIX . "event`
             WHERE `code` = '" . $this->db->escape($code) . "'
             LIMIT 1"
        );

        if ($query->num_rows) {
            $this->db->query(
                "UPDATE `" . DB_PREFIX . "event`
                 SET `description` = '" . $this->db->escape($description) . "',
                     `trigger` = '" . $this->db->escape($trigger) . "',
                     `action` = '" . $this->db->escape($action) . "',
                     `status` = '" . (int)$status . "',
                     `sort_order` = '" . (int)$sort_order . "'
                 WHERE event_id = '" . (int)$query->row['event_id'] . "'"
            );
        } else {
            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "event`
                 SET `code` = '" . $this->db->escape($code) . "',
                     `description` = '" . $this->db->escape($description) . "',
                     `trigger` = '" . $this->db->escape($trigger) . "',
                     `action` = '" . $this->db->escape($action) . "',
                     `status` = '" . (int)$status . "',
                     `sort_order` = '" . (int)$sort_order . "'"
            );
        }
    }

    private function registerPermissions(): void
    {
        /*
         * OpenCart administrator permissions are normally assigned to the
         * user group by the extension installer/admin UI. This file records
         * the required routes in a single place for deployment automation.
         */
        $this->db->query(
            "INSERT IGNORE INTO `" . DB_PREFIX . "setting`
             SET store_id = '0',
                 `code` = 'ohbono_wallet',
                 `key` = 'ohbono_wallet_version',
                 `value` = '0032',
                 serialized = '0'"
        );
    }
}
