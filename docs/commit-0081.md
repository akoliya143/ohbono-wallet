# Commit 0081 — Audit Integration

## Added

- Every successful manual adjustment creates an audit record
- Admin user ID is recorded
- Customer and transaction are linked
- Reason is persisted
- Audit is part of the same database transaction

## Git

```bash
git add .
git commit -m "feat(admin): add controlled wallet adjustment service"

git add .
git commit -m "feat(admin): add wallet adjustment interface"

git add .
git commit -m "feat(wallet): audit manual admin adjustments"

git push
```

## Safety rules

Manual adjustment requires:

1. Admin modify permission
2. Active customer wallet
3. Positive amount
4. Credit or debit direction
5. Mandatory reason
6. Unique reference
7. Atomic transaction
8. Audit record

No manual adjustment should bypass this service.
