# Commit 0006 — Customer Wallet Account

## Added

- Customer wallet account route
- Wallet balance display
- Transaction history
- Transaction pagination
- Customer wallet model
- Customer wallet language strings
- Responsive Bootstrap/Twig wallet template

## Route

```text
account/wallet
```

The controller requires an authenticated customer.

## Security

The customer ID is always taken from the current OpenCart customer session. No customer ID is accepted from the URL.

## Financial integrity

This commit only reads wallet balances and ledger records. It does not modify wallet balances.

All balance-changing operations continue to belong to `WalletService`.

## Next

Commit 0007 will add the admin customer wallet management screen with search, balances, transaction history, credit and debit actions.
