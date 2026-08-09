# Commit 0026 — Order Success Wallet Finalization

## Purpose

Commit 0025 introduced the central atomic wallet service.

Commit 0026 adds the order-finalization orchestration around that service.

The important rule remains:

> Applying wallet during checkout never debits the wallet.

The debit is finalized only after an authoritative OpenCart order exists.

## Flow

### Full wallet

```text
Cart                    ₹2,000
Wallet balance          ₹5,000
        ↓
Apply wallet            ₹2,000
        ↓
Order created
        ↓
Final wallet validation
        ↓
Wallet debit             ₹2,000
Gateway payment              ₹0
        ↓
Wallet balance           ₹3,000
```

### Partial wallet

```text
Cart                    ₹5,000
Wallet balance          ₹2,000
        ↓
Apply wallet            ₹2,000
        ↓
Order created
        ↓
Final wallet validation
        ↓
Wallet debit             ₹2,000
Gateway payment          ₹3,000
        ↓
Wallet balance               ₹0
```

## Idempotency

The final debit uses:

```text
wallet_order.order_id
```

as the idempotency key.

Calling the finalization operation twice for the same order does not debit
the wallet twice.

## Session cleanup

After successful wallet finalization:

```text
ohbono_wallet_use
ohbono_wallet_order_id
```

are removed from the checkout session.

## Important integration point

The provided:

```text
extension/ohbono/checkout/success.finalize
```

is an explicit integration endpoint.

For production, call the same orchestration from the actual OpenCart order
creation/payment-success callback used by the store.

Do NOT put wallet debit logic in:

```text
checkout/success
```

page rendering itself.

The success page can be refreshed/revisited and therefore must never be the
sole trigger for a financial debit.

## Error behavior

If final wallet debit fails because the balance changed before finalization:

```text
Wallet debit → FAILED
```

The order/payment workflow must treat this as a payment-finalization failure
and must not mark the wallet portion as successfully paid.

The existing `wallet_order` uniqueness protects against duplicate callbacks.

## Next

Commit 0027 will add customer wallet account/history pages so customers can
see balance, credits, debits, order references and transaction history from
their OHBONO account area.
