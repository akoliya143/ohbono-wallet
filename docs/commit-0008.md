# Commit 0008 — Admin Transaction Ledger

## Added

- Dedicated transaction ledger screen
- Customer/name/email/ID filter
- Transaction type filter
- Credit/debit filter
- Order ID filter
- Date range filter
- Pagination
- Balance-before and balance-after visibility
- Reference and comment visibility

## Financial integrity

This screen is read-only. It does not edit or delete ledger rows.

Wallet transaction records are intended to remain immutable.

## Next

Commit 0009 will add the wallet checkout calculation layer and a dedicated wallet payment/total integration, including partial wallet usage.
