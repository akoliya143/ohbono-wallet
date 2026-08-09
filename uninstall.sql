-- OHBONO Wallet Pro
-- Uninstall database schema
-- Replace {DB_PREFIX} with the OpenCart database prefix.

DROP TABLE IF EXISTS `{DB_PREFIX}wallet_log`;
DROP TABLE IF EXISTS `{DB_PREFIX}wallet_order`;
DROP TABLE IF EXISTS `{DB_PREFIX}wallet_transaction`;
DROP TABLE IF EXISTS `{DB_PREFIX}wallet_setting`;
DROP TABLE IF EXISTS `{DB_PREFIX}wallet`;
