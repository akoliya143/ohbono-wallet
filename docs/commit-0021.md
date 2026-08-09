# Commit 0021 — Admin Navigation & Permissions

## Added

- Wallet administration navigation
- Dashboard shortcut
- Wallet customer shortcut
- Wallet settings shortcut
- Wallet payment shortcut
- Permission helper
- Admin wallet menu model

## Admin structure

```text
OHBONO Wallet
├── Dashboard
├── Wallet Customers
├── Wallet Settings
└── Wallet Payment
```

## Permission routes

```text
extension/ohbono/module/wallet_dashboard
extension/ohbono/module/wallet_customer
extension/ohbono/module/wallet_settings
extension/ohbono/payment/wallet
```

Both `access` and `modify` permissions are provisioned for the relevant
administrator groups by the permission installer.

## OpenCart note

OpenCart 4 uses the extension/controller route as the permission key. The
actual left navigation rendering depends on the store's installed OpenCart
administration theme and any custom admin menu modifications.

The wallet package therefore keeps navigation data isolated from core admin
templates instead of replacing OpenCart's `common/column_left`.

## Next

Commit 0022 will add the complete transaction ledger administration page with
filters, pagination, customer/order references, credit/debit indicators and
transaction detail inspection.
