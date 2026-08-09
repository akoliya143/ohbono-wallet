# Commit 0051 — Central Wallet Financial Service

## Added

- Central wallet credit/debit service
- Row locking
- Before/after balance recording
- Idempotent transaction references
- Insufficient balance protection
- Admin user attribution
- Order linkage support

## Financial rule

No admin controller directly changes:

```text
wallet.balance
```

Instead:

```text
Admin
  ↓
Wallet Service
  ↓
Locked Wallet Row
  ↓
Transaction Record
  ↓
Updated Balance
```

## Git

```bash
git add .
git commit -m "feat(admin): add customer wallet management screen"

git add .
git commit -m "feat(admin): add wallet adjustment interface"

git add .
git commit -m "feat(wallet): centralize financial balance mutations"

git push
```

## Next

Commit 0052 will focus on wallet transaction/audit administration, including
transaction filtering, admin/user attribution, references, order filtering and
audit-safe history presentation.
