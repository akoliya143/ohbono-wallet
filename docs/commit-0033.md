# Commit 0033 — Wallet Integrity & Reconciliation

## Added

- Read-only wallet integrity service
- Stored balance vs ledger reconciliation
- Duplicate wallet-order detection
- Orphan transaction detection
- Admin wallet health dashboard
- Unhealthy customer report

## Core check

For every wallet:

```text
Stored Wallet Balance
        -
Ledger Credits
        +
Ledger Debits
        =
Expected Ledger Balance
```

The health check compares the stored wallet balance with the calculated
ledger balance.

A difference of less than `0.0001` is treated as zero.

## Important

This commit does NOT automatically repair balances.

That is intentional.

Automatic financial corrections can create additional accounting problems.
The health dashboard identifies discrepancies so an authorized administrator
can investigate them.

## Checks

The dashboard checks:

```text
Wallet count
Customers with positive balance
Transaction count
Stored balance total
Ledger balance total
Balance difference
Duplicate order mappings
Orphan transactions
```

## Next

Commit 0034 will add audit logging and admin adjustment safeguards:
reason-required manual corrections, before/after balances, admin identity,
IP/user-agent metadata and an immutable correction audit record.
