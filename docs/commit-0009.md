# Commit 0009 — Wallet Checkout Calculation Layer

## Added

- OpenCart 4 order-total model for wallet usage
- Wallet usage stored in checkout session
- Partial wallet payment calculation
- Full wallet payment calculation
- Minimum wallet usage
- Maximum wallet usage
- Wallet balance validation during total calculation
- Generic wallet checkout block
- Apply wallet AJAX endpoint
- Remove wallet AJAX endpoint
- Admin total-extension installer configuration

## Important payment flow

For an order of ₹5,000 with ₹3,000 in the customer's wallet:

```text
Order total       ₹5,000
Wallet balance    ₹3,000
Wallet selected   ₹3,000
Remaining total   ₹2,000
```

The ₹3,000 is **not debited yet**.

Commit 0009 only calculates and reserves the requested amount in the checkout session.

The actual wallet debit must happen during order creation, inside a protected database transaction, and must be idempotent.

## OpenCart 4.1.0.3

OpenCart's cart total engine loads enabled total extensions using the `extension/{extension}/total/{code}` model path. The wallet therefore uses:

```text
catalog/model/extension/ohbono/total/wallet.php
```

and contributes a negative total.

## Journal 4

The generic checkout block emits:

```javascript
ohbono:wallet-applied
```

instead of assuming a specific Journal AJAX function.

The dedicated Journal 4 adapter will be added later so Journal's native checkout refresh mechanism is used without changing the wallet calculation engine.

## Security

The apply endpoint:

- Requires a logged-in customer.
- Reads the wallet balance server-side.
- Never trusts a client-supplied wallet balance.
- Caps wallet use at the current cart total.
- Caps wallet use at the current wallet balance.
- Applies configured minimum/maximum limits.

## Next

Commit 0010 will connect wallet usage to order creation and perform the real ledger debit exactly once, then create the `wallet_order` mapping.
