# Commit 0129 — Deployment Validation

## Added

- Deployment file validator
- Admin diagnostic endpoint
- Required-file checks
- No financial/database mutation

## Git

```bash
git add .
git commit -m "security(wallet): add order state guards"

git add .
git commit -m "feat(deploy): add wallet deployment validator"

git push
```

## Important

This batch is still pre-production. The validator confirms extension integrity,
not live checkout success.

Before production:

1. Run deployment validation.
2. Run all regression tests.
3. Install on staging.
4. Run the complete staging matrix.
5. Verify wallet ledger invariants.
6. Verify refunds/reversals.
7. Verify Journal checkout.
8. Verify cross-customer isolation.
9. Take and verify a production backup.
10. Only then enable the wallet payment method.

## Next

0130–0132 should be the final staging execution/evidence package, with no new
financial behavior unless a real staging defect requires a targeted fix.
