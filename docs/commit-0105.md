# Commit 0105 — OpenCart Integration Tests

## Added

- Static integration checks
- Runtime tests for pure security helpers
- Server-side reference generation in wallet capture
- POST enforcement for financial mutations

## Git

```bash
git add .
git commit -m "security: harden wallet input validation"

git add .
git commit -m "feat(admin): add wallet schema diagnostics"

git add .
git commit -m "test(wallet): add OpenCart integration checks"

git push
```

## Important

These tests are intentionally dependency-light. They do not claim that a
complete OpenCart checkout has been integration-tested.

Before production deployment, run the extension inside the actual OpenCart
4.1.x installation with its configured database prefix, customer groups,
admin permissions, checkout flow and Journal theme.

The wallet must remain server-authoritative:

```text
Browser
   |
   v
Request
   |
   v
Authenticated customer/admin
   |
   v
Trusted order state
   |
   v
Wallet service
   |
   v
Database transaction
```
