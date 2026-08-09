# Commit 0020 — Single Installer & Migration Layer

## Purpose

This commit creates a clean installation path for the OHBONO Wallet module.

Instead of manually importing several SQL files and manually registering
events, the installer handles the core database/configuration setup.

## Installation

1. Copy the complete `upload/` directory into the OpenCart root.
2. Copy `install.php` to the OpenCart root.
3. Run:

```bash
php install.php
```

4. Log into OpenCart admin.
5. Refresh modification/cache if required.
6. Configure the OHBONO Wallet settings.
7. Remove `install.php` from the public OpenCart root.

## Existing development batches

The installer creates:

- `wallet`
- `wallet_transaction`
- `wallet_order`

It also creates the canonical:

```text
ohbono_wallet_*
```

settings.

It attempts to migrate the old development settings:

```text
total_wallet_status
total_wallet_allow_checkout
total_wallet_minimum_use
total_wallet_maximum_use
total_wallet_sort_order
```

## Uninstall

Normal uninstall:

```bash
php uninstall.php
```

This removes settings/events but preserves wallet financial tables.

To intentionally delete financial wallet data:

```bash
php uninstall.php --drop-data
```

Do not use `--drop-data` on a production store unless permanent deletion has
been explicitly approved and a backup exists.

## Event

The installer registers:

```text
catalog/model/checkout/order/addOrder/after
        ↓
extension/ohbono/payment/wallet.orderCreated
```

The event is updated rather than duplicated when the installer is run again.

## Important

This installer is intended for the OHBONO Wallet development package.
Before production deployment, the event signature must be verified against
the exact OpenCart 4.1.0.3 build installed on the store.

## Next

Commit 0021 will add the admin menu/permissions integration and extension
registration so Wallet Dashboard, Customers, Transactions, Settings and
Payment configuration are accessible from the OpenCart administration UI.
