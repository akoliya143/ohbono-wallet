# Commit 0066 — Wallet Email Integration Foundation

## Added

- Mail service foundation
- Transaction-aware email dispatcher
- Preference-aware delivery decision
- Financial transaction remains independent of email delivery

## Important

Email must NEVER be part of the critical wallet mutation transaction.

Correct flow:

```text
Wallet mutation
      ↓
Commit transaction
      ↓
Create in-site notification
      ↓
Queue/dispatch email
```

A mail server failure must not undo a wallet credit/debit.

## Git

```bash
git add .
git commit -m "feat(storefront): add wallet email preferences"

git add .
git commit -m "feat(storefront): add wallet preference UI"

git add .
git commit -m "feat(wallet): add email notification integration foundation"

git push
```

## Next

0067–0069 will complete the asynchronous email queue/delivery layer, add
retry handling and admin visibility for failed wallet notification emails.
