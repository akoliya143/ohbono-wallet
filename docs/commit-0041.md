# Commit 0041 — Checkout Order Finalization & Reservation Safety

## Added

- Wallet checkout finalizer
- Wallet transaction-to-order linkage
- Finalized wallet order mapping
- Duplicate finalization protection
- Transaction/customer/reference validation
- Reservation restoration service
- OpenCart order-created event
- Admin order wallet transaction lookup

## Final checkout flow

```text
Cart
  |
  v
Wallet Quote
  |
  v
Wallet Reservation/Debit
  |
  v
OpenCart Order Created
  |
  v
Commit 0041 Finalizer
  |
  +--> wallet_transaction.order_id
  |
  +--> wallet_order.status = 1
```

## Failure handling

If a wallet reservation exists but an order has not been created, the
`checkoutFailure()` integration can restore the reserved amount.

Restoration is idempotent:

```text
CHECKOUT-xxxxxxxx-RESTORE
```

The same reservation cannot be restored twice.

## Important accounting behavior

If an order has already been created but finalization fails, the event does
NOT blindly credit the wallet back.

This avoids the dangerous situation where:

```text
Wallet debit exists
Order exists
Automatic restore happens
```

and later a retry finalizes the order, producing an incorrect double movement.

Such a mismatch is logged and can be found through wallet reconciliation.

## Order linkage

Successful finalization attaches:

```text
wallet_transaction.order_id
wallet_order.order_id
wallet_order.transaction_id
wallet_order.customer_id
wallet_order.amount
wallet_order.reference
```

## Existing extension tree

Commit 0041 updates the existing OHBONO extension architecture. It does NOT
create a new `0041` extension folder.

## Next

Commit 0042 will add the actual OpenCart payment-method integration layer:
payment-method discovery, checkout totals integration, payment confirmation
handling and Journal-compatible checkout presentation.
