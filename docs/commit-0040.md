# Commit 0040 — Wallet Checkout Payment Integration

## Added

- Wallet checkout quote calculation
- Partial wallet payment support
- Maximum wallet-use validation
- Row-locked final balance validation
- Idempotent checkout wallet debit
- Checkout transaction reference
- Wallet restoration endpoint
- Checkout wallet payment UI component
- Transaction indexes for checkout performance

## Checkout behavior

If:

```text
Order Total = ₹1,000
Wallet Balance = ₹600
```

then:

```text
Wallet Applied = ₹600
Remaining Payment = ₹400
```

If:

```text
Order Total = ₹1,000
Wallet Balance = ₹2,000
```

then:

```text
Wallet Applied = ₹1,000
Remaining Payment = ₹0
```

If `ohbono_wallet_maximum_use` is configured, the wallet application is
limited by that value.

## Concurrency protection

The final wallet balance is checked with:

```sql
SELECT ... FOR UPDATE
```

inside a database transaction.

This prevents two concurrent checkout requests from spending the same balance.

## Idempotency

Each checkout wallet debit gets a unique reference:

```text
CHECKOUT-xxxxxxxxxxxxxxxx
```

If the same reference is processed again, the existing transaction is returned
instead of creating another debit.

## Restoration

If checkout is abandoned after wallet debit, the integration can call the
restore endpoint.

The restore operation is itself idempotent using:

```text
CHECKOUT-...-RESTORE
```

## Important integration rule

Commit 0040 provides the wallet payment engine and UI component. It does not
automatically replace the store's checkout total calculation.

The final checkout implementation must:

1. Validate the real cart total.
2. Apply wallet amount.
3. Recalculate the remaining payment method amount.
4. Attach the wallet transaction to the created order.
5. Restore the wallet amount if order creation/payment fails.

Never trust a wallet amount supplied by browser JavaScript.

## Next

Commit 0041 will add order-finalization linkage: attach the reserved wallet
transaction to the created OpenCart order, finalize successful checkout,
restore the wallet on failed order creation and prevent stale wallet
reservations.
