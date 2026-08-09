# Commit 0030 — Admin Wallet Customer Management

## Added

- Admin wallet customer list
- Customer search by name, email or customer ID
- Wallet balance display
- Wallet status display
- Customer wallet detail page
- Wallet summary
- Recent transactions
- Admin credit action
- Admin debit action
- Admin user ID recorded on adjustments
- Atomic wallet service integration

## Admin flow

```text
OHBONO Wallet
    |
    +-- Wallet Customers
            |
            +-- Search customer
            |
            +-- View wallet
                    |
                    +-- Balance
                    +-- Summary
                    +-- Transactions
                    +-- Credit
                    +-- Debit
```

## Credit

Admin can create a wallet credit with:

```text
Amount
Reference
Comment
```

The central wallet service records:

```text
admin_credit
credit
balance_before
balance_after
admin_user_id
```

## Debit

Admin debit uses the same atomic wallet service and cannot make the balance
negative.

The service re-checks the current database balance under a transaction lock.

## Financial safety

The admin controller does not directly update the wallet balance.

All balance changes go through:

```text
OhbonoWalletService
```

This keeps the ledger and balance synchronized.

## Next

Commit 0031 will add wallet settings and configuration administration,
including enable/disable, checkout usage limits, minimum/maximum wallet
usage and transaction display settings.
