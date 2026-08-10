# OpenCart 4.1.x Environment Verification — 0112

Before enabling wallet checkout events, verify the actual target installation.

## Required checks

1. OpenCart version is 4.1.x.
2. `DB_PREFIX` is the expected database prefix.
3. Wallet tables exist.
4. Admin permission routes exist.
5. Customer wallet routes resolve.
6. The installed checkout route is identified.
7. Journal Theme 3.2 is active.
8. Journal checkout AJAX flow is identified.
9. No core files are modified by OHBONO Wallet.

## Do not assume

The extension must not assume that every OpenCart 4.1.x installation has the
same checkout controller or Journal customization.

Record the actual routes before registering events.

## Verification commands

From the OpenCart root:

```bash
php -v
php -l upload/catalog/controller/extension/ohbono/module/wallet_payment.php
php -l upload/system/library/ohbono/wallet_payment_service.php
```

Check the database:

```sql
SHOW TABLES LIKE 'oc_wallet%';
SHOW TABLES LIKE 'oc_event%';
```

Replace `oc_` with the real `DB_PREFIX`.

## Expected wallet tables

```text
wallet
wallet_transaction
wallet_admin_audit
wallet_payment_state
```

## Production rule

Environment verification is read-only. Do not enable financial hooks until the
checkout route and event registration mechanism have been confirmed.
