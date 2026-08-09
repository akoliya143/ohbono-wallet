# Commit 0042 — OpenCart Wallet Payment Integration

## Added

- Wallet payment-method discovery
- `ohbono_wallet` payment method code
- Checkout confirmation endpoint
- Server-side wallet quote validation
- Maximum wallet-use enforcement
- Journal-friendly wallet payment component
- Admin wallet-payment visibility page
- Payment event registration

## Payment code

```text
ohbono_wallet
```

## Checkout flow

```text
OpenCart Checkout
       |
       v
Payment Methods
       |
       v
OHBONO Wallet
       |
       v
Server-side Quote
       |
       v
Wallet Reservation
       |
       v
Order Creation
       |
       v
Commit 0041 Finalization
```

## Security

The payment amount is recalculated server-side.

The browser is not trusted for:

```text
wallet balance
wallet amount
maximum wallet usage
```

The final wallet debit is protected by the wallet row lock introduced in
Commit 0040.

## Journal Theme

The payment template uses standard Bootstrap-compatible markup and can be
placed into the Journal checkout layout/module system.

No Journal core files are modified.

## Important

This commit establishes the wallet payment-method integration layer. It does
not replace the store's complete checkout total/payment orchestration.

The checkout implementation must still pass the real calculated order total
to the wallet confirmation endpoint and finalize the reservation through the
Commit 0041 order event.

## Next

Commit 0043 will add wallet checkout UI state management: automatic quote
refresh when cart totals change, applied-wallet state, remaining payment
amount, remove-wallet action and protection against stale checkout sessions.
