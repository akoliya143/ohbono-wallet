# Commit 0099 — Payment Reconciliation

## Added

- Payment-state classification
- Detection of wallet captures needing reconciliation
- Admin reconciliation query
- Wallet refund endpoint
- Static safety checks

## Git

```bash
git add .
git commit -m "feat(wallet): add reversal primitive"

git add .
git commit -m "feat(payment): add order wallet refunds"

git add .
git commit -m "feat(admin): add wallet payment reconciliation"

git push
```

## Critical behavior

A wallet reversal is a compensating credit:

```text
Original:
Wallet - ₹500
Transaction #100

Reversal:
Wallet + ₹500
Transaction #125
```

Transaction #100 is never changed or deleted.

## Reconciliation

If a wallet payment succeeds but the secondary payment fails, the order
workflow should mark the payment as requiring reconciliation and invoke an
approved reversal/refund process.

Do not automatically reverse money merely because an order is pending unless
the project's payment-state rules explicitly define that state as failed.

## Next

0100–0102 should add customer-facing refund visibility, payment-state
synchronization hooks and stronger reconciliation tooling before final security
hardening and production packaging.
