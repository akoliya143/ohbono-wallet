# Commit 0011 — Checkout Integration Hardening

## Added

- Server-side wallet balance lookup for checkout
- Safe wallet amount calculation
- Checkout session wallet amount
- Wallet apply/remove endpoints
- Generic checkout refresh event
- Improved AJAX handling
- Credentials included for same-origin requests
- Journal 4 integration hook
- Current cart total calculation
- Admin event registration refresh

## Journal 4

The extension intentionally does not call undocumented Journal functions directly.

The checkout component emits:

```text
ohbono:wallet-applied
```

A Journal-specific adapter can listen to that event and invoke the native Journal refresh lifecycle.

This keeps the core wallet extension independent from Journal internals.

## Important

The wallet amount in the checkout session is not a financial debit.

The authoritative balance check and ledger debit remain server-side and must happen during the order/payment transaction.

## Next

Commit 0012 will add the final payment-state handling around wallet-funded orders, including zero-remaining-total handling and payment-method validation.
