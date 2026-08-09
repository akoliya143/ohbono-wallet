# Commit 0007 — Admin Wallet Management

## Added

- Admin wallet list
- Customer search by name, email or customer ID
- Wallet status filter
- Customer wallet detail screen
- Current balance display
- Transaction history
- Admin credit operation
- Admin debit operation
- Permission checks
- Success/error messages

## Routes

```text
extension/ohbono/module/wallet_customer
extension/ohbono/module/wallet_customer.form
extension/ohbono/module/wallet_customer.credit
extension/ohbono/module/wallet_customer.debit
```

## Financial operations

Admin credit/debit calls `WalletService`.

The controller does not directly update the wallet balance.

## Next

Commit 0008 will improve administration navigation/permissions and add a dedicated transaction management/reporting screen.
