# Commit 0012 — Wallet Order Payment State

## Added

- `WalletOrder` idempotency helper
- `WalletOrderService`
- `wallet_order` mapping table
- Wallet order payment model
- Session-wallet cleanup helper
- Safe order-level wallet debit entry point

## Order flow

The intended final flow is:

```text
Checkout cart
    ↓
Customer applies wallet
    ↓
Session stores requested amount
    ↓
Order is created
    ↓
Wallet amount is revalidated
    ↓
Wallet is debited once
    ↓
wallet_transaction is created
    ↓
wallet_order mapping is created
    ↓
Remaining order amount is paid by normal payment method
```

## Zero-total order

If wallet usage covers the complete order total:

```text
Order total     ₹5,000
Wallet used     ₹5,000
Remaining       ₹0
```

The order should use the dedicated wallet payment flow rather than sending a zero amount to an external gateway.

The final payment-method adapter will be added in the next commit.

## Double-debit protection

`wallet_order.order_id` is unique.

Additionally, the service checks for an existing successful mapping before attempting another debit.

## Important

Do not debit the wallet from the browser.

Do not trust the amount stored in JavaScript.

The final amount must be recalculated and checked against the wallet balance on the server.

## Next

Commit 0013 will add the dedicated `Wallet Payment` method, including zero-total checkout support and payment-method validation.
