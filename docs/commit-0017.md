# Commit 0017 — Admin Wallet Dashboard

## Added

- Wallet dashboard
- Total wallet liability
- Number of active wallet customers
- Lifetime wallet credits
- Lifetime wallet debits
- Recent transaction list
- Quick link to wallet customers
- Quick link to transaction ledger

## Dashboard meaning

### Total Wallet Liability

This is the sum of all active wallet balances:

```sql
SUM(wallet.balance)
```

This represents the current outstanding customer-wallet balance.

### Lifetime Credits

All credit transactions recorded in the wallet ledger.

### Lifetime Debits

All debit transactions recorded in the wallet ledger.

These are lifetime ledger metrics and are intentionally not treated as current
liability.

## Next

Commit 0018 will add the complete admin wallet customer management screen,
including direct credit/debit controls, customer balance, customer history and
transaction creation safeguards.
