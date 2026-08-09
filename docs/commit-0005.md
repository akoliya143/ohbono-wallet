# Commit 0005 — OpenCart Integration Foundation

## Added

- `WalletFactory`
- `WalletEvent`
- Admin wallet settings controller
- Admin wallet settings model
- Admin language strings
- Journal/OpenCart-friendly Bootstrap/Twig settings page
- Extension install/uninstall event registration
- Automatic wallet creation after customer creation

## Admin settings

- Wallet status
- Checkout availability
- Partial payment
- Full payment
- Refund-to-wallet flag
- Minimum wallet usage
- Maximum wallet usage
- Sort order

## Important

The customer-created event only creates an empty wallet. It never changes a balance.

All financial balance changes remain inside `WalletService`.

## Next

Commit 0006 will add the customer-facing wallet account page and transaction statement.
