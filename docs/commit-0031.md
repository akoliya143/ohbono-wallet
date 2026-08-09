# Commit 0031 — Wallet Settings & Configuration

## Added

- Admin wallet settings page
- Enable/disable wallet
- Enable/disable wallet checkout
- Minimum wallet usage
- Maximum wallet usage
- Customer transaction history limit
- Wallet sort order
- Validation for minimum vs maximum usage

## Settings

```text
ohbono_wallet_status
ohbono_wallet_allow_checkout
ohbono_wallet_minimum_use
ohbono_wallet_maximum_use
ohbono_wallet_history_limit
ohbono_wallet_sort_order
```

## Recommended production defaults

```text
Wallet Status:              Enabled
Checkout Wallet:            Enabled
Minimum Usage:              0
Maximum Usage:              0
History Limit:              20
Sort Order:                 100
```

A maximum of `0` means no configured maximum.

## Important

The settings page changes configuration only.

It does not directly modify:

```text
wallet.balance
wallet_transaction
wallet_order
```

Financial operations continue to go through the central wallet service.

## Permissions

The admin user must have:

```text
access:
extension/ohbono/module/wallet_settings

modify:
extension/ohbono/module/wallet_settings
```

Add the route to the appropriate administrator user group before exposing the
settings page.

## Next

Commit 0032 will add installer/uninstaller hardening, idempotent database
migration checks, default settings initialization, event registration and
safe upgrade handling for the OHBONO Wallet extension.
