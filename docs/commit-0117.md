# Commit 0117 — Staging Checkout Wiring

## Added

- Wallet-only staging test
- Partial-wallet staging test
- External payment failure scenario
- Retry/idempotency scenario
- Browser-refresh scenario
- Payment math tests

## Git

```bash
git add .
git commit -m "feat(integration): add verified wallet event registry"

git add .
git commit -m "feat(journal): connect wallet checkout bridge"

git add .
git commit -m "test(staging): wire wallet checkout scenarios"

git push
```

## Important

Do not run the event installer against production until the target OpenCart
4.1.x event API and Journal 3.2 checkout flow have been verified.

The financial service remains the only component allowed to mutate wallet
balances.

## Next

0118–0120 should complete staging observability and reconciliation:
transaction/payment-state logging, admin order/payment visibility, duplicate
callback detection, and a production-readiness gate.
