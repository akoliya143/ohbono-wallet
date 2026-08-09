# OHBONO Wallet Pro — Database Design

## Tables

### `wallet`
Stores the customer's current wallet balance.

The `customer_id` column is unique, so each customer has one wallet.

### `wallet_transaction`
Immutable wallet ledger.

Every credit/debit operation creates one row. The row records:

- Balance before
- Transaction amount
- Balance after
- Direction
- Transaction type
- Order reference
- Human-readable comment
- Administrator/user responsible
- Timestamp

The wallet balance must never be changed without creating a corresponding ledger row.

### `wallet_order`
Maps an order to the wallet transaction used to pay that order.

This prevents ambiguity when an order is paid partly or fully from the wallet.

### `wallet_setting`
Stores extension settings independently of OpenCart's global setting table.

### `wallet_log`
Stores operational diagnostics and error context without exposing sensitive payment information.

## Money handling

Wallet amounts use `DECIMAL(15,4)`. PHP code must not use floating-point arithmetic for persisted wallet amounts. Database values should be converted to decimal strings before monetary calculations that affect the ledger.

## Concurrency

Debit operations must lock the wallet row inside a database transaction before checking the balance:

1. Begin transaction.
2. Select the wallet row using `FOR UPDATE`.
3. Validate status and available balance.
4. Insert the transaction ledger row.
5. Update the wallet balance.
6. Commit.

If any step fails, roll back the entire operation.
