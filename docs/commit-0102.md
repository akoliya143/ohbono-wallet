# Commit 0102 — Admin Reconciliation UI

## Added

- Admin reconciliation list
- Wallet transaction linkage
- Payment-state visibility
- Remaining payment visibility
- Explicit manual-review boundary

The screen does not automatically move money.

## Git

```bash
git add .
git commit -m "feat(storefront): add wallet refund history"

git add .
git commit -m "feat(payment): add wallet payment state synchronization"

git add .
git commit -m "feat(admin): add wallet reconciliation screen"

git push
```

## Important

`wallet_payment_state` is a workflow/state table, not a financial ledger.

The financial ledger remains:

```text
wallet_transaction
```

The workflow state is:

```text
wallet_payment_state
```

Keeping these separate prevents order/payment-state changes from rewriting
financial history.
