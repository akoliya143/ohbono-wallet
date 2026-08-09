# Commit 0015 — Partial Wallet & Refund Foundation

## Added

- Wallet amount persisted against an order
- Wallet-funded order lookup
- Wallet refund service
- Immutable refund credit transaction
- Refunded wallet-order state
- Customer-safe wallet order lookup
- Admin wallet-order lookup model

## Partial wallet example

```text
Order total              ₹5,000
Wallet amount            ₹3,000
External payment         ₹2,000
```

The wallet amount belongs to the order and can later be reconciled against
the external payment.

## Refund example

If the customer is refunded for the complete order:

```text
Original wallet debit     ₹3,000
Wallet refund credit      ₹3,000
Net wallet impact             ₹0
```

The original debit is never edited or deleted. A new credit transaction is
created with type `refund`.

## Important production rule

Do not automatically refund the wallet merely because an order changes to
a generic cancelled status unless the business rule explicitly says that
status represents a refundable payment.

A proper refund policy should distinguish:

- Cancelled before payment
- Payment failed
- Partially refunded
- Fully refunded
- Manual refund
- Gateway refund

## Partial-wallet orchestration

This commit stores the wallet portion and provides the refund foundation.

The external payment method still owns its own gateway transaction.

The final production integration should call the wallet debit only when the
external-payment/order workflow has successfully reached the appropriate
payment state.

## Next

Commit 0016 will add customer-facing wallet history/balance pages and the
Journal 4-compatible account integration so customers can see credits,
debits, order references and running balances.
