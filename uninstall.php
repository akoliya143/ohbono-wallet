<?php
/**
 * OHBONO Wallet uninstaller.
 *
 * Financial data is NOT deleted by default.
 *
 * Removing the extension should not silently destroy customer balances or
 * the immutable wallet ledger.
 */

class ControllerExtensionOhbonoUninstall extends Controller
{
    public function index(): void
    {
        $this->removeEvents();
        $this->removeSettings();

        /*
         * Intentionally preserve:
         *
         *   oc_wallet
         *   oc_wallet_transaction
         *   oc_wallet_order
         *
         * These tables contain financial records and must be archived or
         * explicitly deleted by a separate administrator-approved migration.
         */

        $this->response->setOutput(
            'OHBONO Wallet uninstallation completed. Financial wallet data was preserved.'
        );
    }

    private function removeEvents(): void
    {
        $this->db->query(
            "DELETE FROM `" . DB_PREFIX . "event`
             WHERE `code` LIKE 'ohbono_wallet_%'"
        );
    }

    private function removeSettings(): void
    {
        $this->db->query(
            "DELETE FROM `" . DB_PREFIX . "setting`
             WHERE `code` = 'ohbono_wallet'"
        );
    }
}
