# Commit 0003

## Database foundation

This commit introduces the complete initial wallet database schema.

### Added

- Wallet account table
- Immutable transaction ledger
- Order-to-wallet mapping
- Wallet-specific settings
- Wallet diagnostic log
- Initial wallet settings

### Design decisions

- `DECIMAL(15,4)` is used for monetary values.
- Wallet/customer relationship is unique.
- Transaction rows are append-only.
- Indexes are included for customer, order, date, type and wallet lookups.
- No foreign keys are used so the extension remains compatible with different OpenCart installations and existing database cleanup strategies.

## Next

Commit 0004 will implement the PHP wallet domain layer and repository with transactional credit/debit operations.
