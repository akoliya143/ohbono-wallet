# Commit 0025 — Final Wallet Payment Orchestration

## Added

- Central wallet service
- Atomic credit/debit operations
- Database-level wallet row locking
- Final balance revalidation
- Order wallet mapping
- Idempotent order debit
- Wallet payment method model
- Finalize helper
- Order orchestration helper

## Critical security rule

The browser's wallet amount is never trusted as the final debit amount.

Checkout:

```text
Browser amount
     ↓
Session
     ↓
Order created
     ↓
Final wallet amount
     ↓
Database balance revalidation
     ↓
START TRANSACTION
     ↓
SELECT wallet FOR UPDATE
     ↓
Check balance
     ↓
Insert immutable transaction
     ↓
Update wallet balance
     ↓
Insert wallet_order mapping
     ↓
COMMIT
```

## Idempotency

`wallet_order.order_id` is unique.

If the same order-processing callback runs twice, the existing wallet
transaction is returned instead of debiting the wallet a second time.

## Full wallet payment

Example:

```text
Order total             ₹2,000
Wallet balance          ₹5,000

Final wallet debit      ₹2,000
Gateway payment             ₹0
```

## Partial wallet payment

Example:

```text
Order total             ₹5,000
Wallet balance          ₹2,000

Final wallet debit      ₹2,000
Gateway payment         ₹3,000
```

The gateway portion is still handled by the selected OpenCart payment
extension.

## Abandoned checkout

Applying wallet does NOT debit the wallet.

No transaction is created until the final order workflow calls the central
service.

Therefore abandoned carts do not create wallet debits.

## Important OpenCart integration note

The exact OpenCart 4.1.0.3 order/payment callback route should be wired to
the store's final order-created/payment-success workflow.

Do not call `debitForOrder()` merely when the customer opens checkout.

The service itself is ready for that integration and is intentionally
idempotent.

## Next

Commit 0026 will wire the wallet debit into the OpenCart order-success
workflow, including full-wallet orders and partial-wallet + gateway orders,
with rollback/error handling and wallet session cleanup.
