# Commit 0045 — Checkout Security Hardening

## Added

- Server-side maximum wallet-use validation
- Existing reservation protection
- Customer ownership validation
- Transaction/reference validation
- Stale-session cleanup
- No browser-controlled wallet amount
- No direct customer balance mutation
- Admin settings visibility

## Security rule

The browser can request:

```text
"Use wallet"
```

It cannot decide:

```text
How much money may be spent.
```

The server calculates and validates the permitted amount against the current
wallet balance and configured maximum.

## Existing extension tree

These commits continue the same OHBONO Wallet extension.

No `0043`, `0044`, or `0045` application folders are created.

## Git commits

```bash
git add .
git commit -m "feat(checkout): add dynamic wallet state and removal"

git add .
git commit -m "feat(wallet): add reservation expiry and restoration"

git add .
git commit -m "security(checkout): harden wallet payment validation"

git push
```

## Next

Commit 0046 will focus on the wallet administration layer: proper editable
configuration, customer wallet lookup, manual credit/debit controls with
permissions, and audit logging integration.
