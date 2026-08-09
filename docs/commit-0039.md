# Commit 0039 — Customer Wallet Dashboard

## Added

- Customer wallet dashboard
- Current wallet balance
- Available wallet amount
- Recent five transactions
- Refund summary
- Total wallet refunds
- Refund count
- Responsive storefront presentation
- Journal-friendly card/table layout

## Dashboard

The customer can now see:

```text
Current Wallet Balance
Available Wallet Amount
Total Wallet Refunds
Refund Count
Recent Transactions
```

## Available amount

The available amount respects:

```text
ohbono_wallet_maximum_use
```

If maximum wallet usage is `0`, the full wallet balance is available.

If a maximum is configured:

```text
Available = MIN(wallet balance, configured maximum)
```

This is a display calculation only. Checkout must still independently validate
the final wallet amount before applying payment.

## Security

The dashboard requires:

```text
Logged-in customer
Wallet enabled
```

Customers cannot modify their wallet from this page.

## Theme

The dashboard uses standard Bootstrap-compatible classes and avoids OpenCart
core template changes, making it suitable for Journal Theme 3.x integration.

## Next

Commit 0040 will add wallet checkout payment integration: apply wallet
balance during checkout, partial wallet payment support, minimum/maximum
validation, order transaction linkage and safe rollback behavior.
