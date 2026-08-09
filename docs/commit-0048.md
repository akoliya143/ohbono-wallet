# Commit 0048 — Manual Wallet Adjustment Controls

## Added

- Admin-only wallet credit/debit endpoint
- Mandatory adjustment reason
- Unique admin adjustment reference
- Central wallet service usage
- Admin user ID passed to the transaction/audit layer
- Zero-value adjustment rejection

## Rules

Positive amount:

```text
Credit wallet
```

Negative amount:

```text
Debit wallet
```

Every adjustment requires:

```text
Customer ID
Amount
Reason
```

No direct balance update is performed by the controller.

All financial changes continue through the central wallet service.

## Git

```bash
git add .
git commit -m "feat(admin): add wallet configuration controls"

git add .
git commit -m "feat(admin): add customer wallet administration"

git add .
git commit -m "feat(admin): add permissioned manual wallet adjustments"

git push
```

## Next

Commit 0049 will add the proper admin wallet customer screen with searchable
customer selection, balance display, transaction history and adjustment form,
followed by the next administration hardening work.
