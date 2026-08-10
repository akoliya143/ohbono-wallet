# Commit 0084 — Customer Wallet History

## Added

- Customer-facing wallet transaction history
- Logged-in customer ownership protection
- Credit/debit presentation
- Running balance-after visibility
- Order/reference information

A customer cannot request another customer's wallet history because the
customer ID is always taken from the authenticated customer session.

## Git

```bash
git add .
git commit -m "feat(admin): add wallet audit log"

git add .
git commit -m "feat(admin): improve wallet transaction review"

git add .
git commit -m "feat(storefront): add customer wallet history"

git push
```
