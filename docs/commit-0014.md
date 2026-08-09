# Commit 0014 — Final Wallet Order Debit Integration

## Added

- Order-created wallet payment event
- Final server-side order amount validation
- Customer validation against the created order
- Wallet payment amount validation against final order total
- Atomic wallet debit entry point
- Wallet transaction -> order mapping
- Wallet session cleanup after successful debit
- Admin install/uninstall event registration

## Final Wallet-only flow

```text
Customer selects Wallet
        ↓
Checkout validates wallet balance
        ↓
Order is created
        ↓
order_id exists
        ↓
Wallet event runs
        ↓
Final order total is read from DB
        ↓
Wallet balance is revalidated
        ↓
WalletService locks wallet
        ↓
Wallet transaction created
        ↓
wallet_order created
        ↓
Wallet session cleared
```

## Example

```text
Order total       ₹5,000
Wallet balance    ₹6,000
Wallet used       ₹5,000
Remaining             ₹0
```

The wallet ledger records a ₹5,000 debit and `wallet_order` maps that debit to the order.

## Double debit

The `wallet_order.order_id` unique key and `WalletOrderService` idempotency check prevent the same order from being debited twice.

## Important implementation note

This commit registers against:

```text
catalog/model/checkout/order/addOrder/after
```

The exact argument/output signature must be verified against the installed OpenCart 4.1.0.3 build before production deployment. If the build exposes the new order ID differently, only `resolveOrderId()` needs adjustment.

## Partial wallet payment

A partial wallet payment is deliberately NOT converted into the Wallet-only payment method.

For example:

```text
Order       ₹5,000
Wallet      ₹3,000
Remaining   ₹2,000
```

The ₹3,000 wallet deduction belongs to the final order payment orchestration and must be combined with the selected external payment method. That combined flow is the next integration stage.

## Next

Commit 0015 will add proper partial-wallet + external-payment orchestration, order status/refund handling, and wallet refund/reversal support.
