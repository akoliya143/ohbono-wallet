# OHBONO Wallet Pro

## Commit 0004

Core wallet domain layer.

### Added

- Wallet service
- Repository
- Transaction constants
- Wallet logger
- Wallet exception handling
- Transaction-safe credit/debit operations

### Usage

The OpenCart integration layer will construct the service through the registry in the next commits.

Balance-changing operations must use `WalletService::credit()` or `WalletService::debit()`.

Do not update `oc_wallet.balance` directly.
