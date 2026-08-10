<?php
/**
 * OHBONO Wallet integration configuration.
 *
 * Keep project-specific switches here rather than modifying OpenCart core.
 */

define('OHBONO_WALLET_CODE', 'ohbono_wallet');
define('OHBONO_WALLET_VERSION', '1.0.1');

define(
    'OHBONO_WALLET_PAYMENT_ROUTE',
    'extension/ohbono/module/wallet_payment|capture'
);

define(
    'OHBONO_WALLET_STATE_ROUTE',
    'extension/ohbono/module/wallet_payment_state|update'
);

define(
    'OHBONO_WALLET_RECONCILIATION_ROUTE',
    'extension/ohbono/module/wallet_reconciliation'
);
