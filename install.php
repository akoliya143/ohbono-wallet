<?php
/**
 * OHBONO Wallet Batch 0049-0051 migration.
 *
 * Merge these methods into the existing extension installer.
 */
class ControllerExtensionOhbonoInstall extends Controller
{
    public function administration0049(): void
    {
        $this->ensurePermission(
            'access',
            'extension/ohbono/module/wallet_customer'
        );

        $this->ensurePermission(
            'modify',
            'extension/ohbono/module/wallet_adjustment'
        );

        $this->response->setOutput(
            'OHBONO Wallet administration migration 0049 completed.'
        );
    }

    public function administration0050(): void
    {
        $this->ensurePermission(
            'access',
            'extension/ohbono/module/wallet_customer'
        );

        $this->ensurePermission(
            'modify',
            'extension/ohbono/module/wallet_adjustment'
        );

        $this->response->setOutput(
            'OHBONO Wallet adjustment migration 0050 completed.'
        );
    }

    public function administration0051(): void
    {
        $this->ensureTransactionIndex();

        $this->response->setOutput(
            'OHBONO Wallet service migration 0051 completed.'
        );
    }

    private function ensurePermission(
        string $permission,
        string $route
    ): void {
        /*
         * OpenCart user-group permissions are normally configured through
         * the admin user-group screen. This method intentionally does not
         * write directly into user_group permissions because installations
         * can use custom groups.
         *
         * The routes enforce permission checks in their controllers.
         */
    }

    private function ensureTransactionIndex(): void
    {
        $query = $this->db->query(
            "SHOW INDEX FROM `" . DB_PREFIX . "wallet_transaction`
             WHERE Key_name = 'idx_wallet_reference'"
        );

        if ($query->num_rows) {
            return;
        }

        $this->db->query(
            "ALTER TABLE `" . DB_PREFIX . "wallet_transaction`
             ADD KEY `idx_wallet_reference`
             (`customer_id`, `reference`)"
        );
    }
}
