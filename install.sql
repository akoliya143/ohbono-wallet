-- Commit 0029
-- Event registration for OHBONO customer account wallet navigation.
--
-- This statement is safe only when the extension installer controls event
-- creation. Do not run it blindly on a database where the same code already
-- exists.

INSERT INTO `oc_event`
SET `code` = 'ohbono_wallet_account_menu',
    `description` = 'OHBONO Wallet customer account navigation',
    `trigger` = 'catalog/controller/account/account/after',
    `action` = 'extension/ohbono/event/account.account',
    `status` = 1,
    `sort_order` = 100;
