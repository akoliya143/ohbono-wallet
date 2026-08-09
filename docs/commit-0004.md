# Commit 0004 — Core Wallet Engine

This commit adds the first real wallet domain implementation.

## Added

- `WalletException`
- `WalletHelper`
- `WalletTransaction`
- `WalletRepository`
- `WalletLogger`
- `WalletService`

## Supported operations

- Create wallet
- Read balance
- Check spending availability
- Credit wallet
- Debit wallet
- Read transaction history
- Read transaction counts
- Read/write wallet settings

## Concurrency protection

Credit and debit operations:

1. Start a database transaction.
2. Lock the customer's wallet row with `SELECT ... FOR UPDATE`.
3. Validate wallet state.
4. Validate debit balance.
5. Create immutable ledger entry.
6. Update current wallet balance.
7. Commit.

Failures roll back the complete operation.

## Important

This commit is the domain engine only. It does not yet register OpenCart events or expose administrative/customer UI.

Those are implemented in later commits.
