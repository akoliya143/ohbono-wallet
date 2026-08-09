# Commit 0018 — Admin Wallet Customer Management

## Added

- Wallet customer list
- Customer search by name
- Customer search by email
- Customer search by ID
- Current wallet balance
- Wallet status
- Customer wallet management page
- Manual credit
- Manual debit
- Reference field
- Comment field
- Customer wallet transaction history
- Balance before/after display

## Financial safety

All manual adjustments go through the central `WalletService`.

The admin controller does not directly update the wallet balance.

This ensures:

- Ledger transaction creation
- Balance locking
- Balance validation
- Debit-over-balance prevention
- Consistent transaction metadata

## Manual credit

Example:

```text
Current balance     ₹2,000
Admin credit        ₹1,000
--------------------------
New balance         ₹3,000
```

## Manual debit

Example:

```text
Current balance     ₹3,000
Admin debit         ₹500
--------------------------
New balance         ₹2,500
```

A debit larger than the available wallet balance must be rejected by the
central wallet service.

## Next

Commit 0019 will add wallet settings/configuration, extension installation
checks, permissions, menu integration and configurable wallet behavior.
