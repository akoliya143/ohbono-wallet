# Commit 0037 — Refund History & Customer Messaging

## Added

### Customer

- Wallet transaction history
- Dedicated wallet refund history
- Refund amount
- Refund reference
- Original order ID
- Refund transaction ID

### Admin

- Wallet refund history screen
- Order ID filter
- Customer ID filter
- Date range filters
- Refund transaction linkage
- Customer information

## Customer refund flow

A customer can see:

```text
Wallet Refund
Amount
Original Order
Refund Reference
Date
```

The customer-facing page is read-only.

## Admin refund flow

The admin refund screen is also read-only. Refund creation continues to use
the Commit 0036 refund service so the idempotency rules remain centralized.

## Important

No customer-facing endpoint can create a refund.

Refund creation remains an authenticated administrative/order-return workflow.

## Next

Commit 0038 will add OpenCart event registration and customer account
navigation integration so the wallet and refund history pages can be reached
normally from the storefront without manually entering routes.
