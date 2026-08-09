# Commit 0010 — Atomic Wallet Debit During Order Creation

## Added

- Order-specific wallet debit
- `wallet_order` idempotency check
- Atomic debit + order mapping transaction
- Order creation before-event validation
- Order creation after-event wallet debit
- Wallet-payment failure handling
- OpenCart 4.1 event registration format
- Safer event registration for older OpenCart versions

## Payment flow

For:

```text
Order total:    ₹5,000
Wallet:         ₹3,000
Wallet selected ₹3,000
```

the checkout total remains:

```text
Wallet:         -₹3,000
Customer pays:   ₹2,000
```

When `addOrder()` executes:

1. `addOrder/before` checks that the wallet still has enough balance.
2. OpenCart creates the order.
3. `addOrder/after` obtains the new order ID.
4. `WalletService::debitForOrder()` starts a DB transaction.
5. Existing `wallet_order` mapping is checked.
6. Wallet row is locked using `FOR UPDATE`.
7. Balance is checked again.
8. Wallet ledger debit is inserted.
9. Wallet balance is updated.
10. `wallet_order` mapping is inserted.
11. All changes commit together.

## Double-debit protection

If the after-event executes again for the same order, the existing `wallet_order` record is detected and the existing transaction ID is returned.

The wallet is therefore not debited twice.

## Failure handling

If the wallet debit cannot be completed after the order has been created, the extension attempts to put the order into status `0` and propagates the wallet error.

The wallet amount is cleared from the checkout session.

No wallet money is removed when the debit transaction fails.

## OpenCart 4.1 compatibility

OpenCart 4.1 changed the event registration API. This commit registers events using the array-based API for versions >= 4.0.1.0 and keeps a compatibility branch for older OpenCart versions.

The OpenCart event system supports before and after model triggers, with after events receiving the method output. citeturn1search1turn1search0

## Next

Commit 0011 will integrate the wallet block into the actual Journal 4 checkout UI and refresh Journal's totals after wallet apply/remove.
