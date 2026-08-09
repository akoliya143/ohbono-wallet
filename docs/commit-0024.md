# Commit 0024 — Journal 4 Checkout Refresh Adapter

## Added

- Journal 4-safe wallet checkout adapter
- Dedicated wallet JavaScript
- Checkout refresh event bridge
- Wallet display synchronization
- Remaining payable amount display
- No modification of Journal core files

## Architecture

```text
Wallet Apply
     |
     v
OHBONO AJAX
     |
     v
session.data[ohbono_wallet_use]
     |
     v
ohbonoWalletChanged
     |
     v
Journal 4 Adapter
     |
     v
Store-specific checkout refresh
```

## Why the adapter is separate

Journal 4 checkout implementations can differ depending on:

- checkout layout
- Journal settings
- custom payment modules
- one-page checkout configuration
- third-party checkout modifications

Therefore this module does not overwrite Journal's JavaScript.

A store-specific Journal refresh can be supplied through:

```javascript
window.ohbonoJournalWalletRefresh = function () {
    // Call the exact Journal 4 checkout refresh method here.
};
```

The wallet module then calls that function automatically after Apply/Remove.

## Events

The module provides:

```text
ohbonoWalletChanged
ohbonoWalletCheckoutUpdating
ohbonoWalletCheckoutUpdated
ohbonoWalletRefreshCheckout
ohbonoWalletRefreshInfo
```

## Important

The exact Journal 4 checkout refresh method should be verified against the
installed Journal 4 build before production deployment.

The financial wallet debit still occurs only in the final order/payment
workflow.

## Next

Commit 0025 will add the final wallet payment/order orchestration:
full-wallet payment, partial-wallet + gateway payment, final balance
revalidation, order mapping, idempotency and abandoned-checkout protection.
