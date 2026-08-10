<?php
/**
 * OHBONO Wallet uninstaller.
 *
 * Financial tables are intentionally NOT removed automatically.
 * Uninstalling the extension must not destroy wallet/accounting history.
 */

if (!defined('DIR_SYSTEM')) {
    require_once 'config.php';
}

echo 'OHBONO Wallet uninstall completed. Financial data was preserved.' .
    PHP_EOL;
