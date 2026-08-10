# Commit 0108 — Checkout Smoke Tests

## Added

- Framework-independent checkout lifecycle checks
- Full wallet payment case
- Partial wallet + secondary payment case
- Wallet capture + failed secondary payment case
- No-wallet case
- Invalid over-wallet case

## Git

```bash
git add .
git commit -m "feat(install): harden wallet installation"

git add .
git commit -m "feat(admin): initialize wallet permissions"

git add .
git commit -m "test(checkout): add wallet payment smoke tests"

git push
```

## Important

The smoke tests are not a substitute for a live OpenCart checkout test.

Before production:

1. Install on a staging OpenCart 4.1.x instance.
2. Create a test customer.
3. Credit the wallet.
4. Place a wallet-only order.
5. Place a partial-wallet order.
6. Force secondary payment failure.
7. Verify reconciliation state.
8. Reverse the wallet transaction.
9. Verify customer balance and ledger.
10. Verify admin permissions.
11. Test with Journal checkout customization enabled.

Never test financial deduction/reversal directly on production.
