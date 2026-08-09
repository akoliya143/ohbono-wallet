-- OHBONO Wallet uninstall policy.
--
-- Financial tables are intentionally NOT dropped.
-- Wallet balances and ledger history are financial records.
--
-- Remove extension events and configuration only.

DELETE FROM `oc_event`
WHERE `code` LIKE 'ohbono_wallet_%';

DELETE FROM `oc_setting`
WHERE `code` = 'ohbono_wallet';

-- DO NOT DROP:
-- oc_wallet
-- oc_wallet_transaction
-- oc_wallet_order
