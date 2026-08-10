# Commit 0087 — Wallet Display Consistency

## Added

- Central currency formatting helper
- Wallet dashboard currency formatting
- OpenCart active-currency usage
- Static test coverage for the customer history layer

## Git

```bash
git add .
git commit -m "feat(storefront): paginate wallet history"

git add .
git commit -m "feat(storefront): add wallet transaction details"

git add .
git commit -m "feat(wallet): standardize wallet currency display"

git push
```

## Important

Wallet database values should not be treated as formatted display strings.
Formatting belongs at the presentation boundary using OpenCart's configured
currency context.
