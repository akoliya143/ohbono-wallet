# Commit 0111 — Staging Deployment Checklist

## Added

- OpenCart 4.1.x staging checklist
- Journal Theme 3.2 integration notes
- Checkout scenario matrix
- Event-definition tests
- Staging smoke-test list

## Git

```bash
git add .
git commit -m "feat(integration): add OpenCart wallet event boundary"

git add .
git commit -m "feat(install): add wallet event definitions"

git add .
git commit -m "test(staging): add wallet checkout integration plan"

git push
```

## Important

Do not register speculative events against production until the exact
OpenCart 4.1.x event table/API and the installed Journal checkout flow have
been verified.

This batch intentionally separates event definitions from the actual
registration call so we do not silently create broken hooks.

## Next

0112–0114 should verify the real OpenCart 4.1.x environment and then wire the
confirmed events, Journal checkout UI and final checkout callback.
