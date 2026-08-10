# Commit 0090 — Non-Mutating Checkout Totals API

## Added

- Checkout wallet amount calculation endpoint
- Authenticated customer requirement
- Requested amount validation
- Remaining total calculation
- No wallet mutation

## Git

```bash
git add .
git commit -m "feat(checkout): add wallet availability calculation"

git add .
git commit -m "feat(checkout): add wallet checkout presentation"

git add .
git commit -m "feat(checkout): add non-mutating wallet totals API"

git push
```

## Important

This batch intentionally does NOT charge the wallet.

The final wallet deduction must happen only after the checkout/order layer has
validated the order, customer, totals and payment state inside an atomic
server-side transaction.

Never trust the wallet amount submitted by JavaScript as authorization.
