# Commit 0013 — Dedicated Wallet Payment Method

## Added

- OpenCart payment method controller
- OpenCart payment method model
- Admin payment-method settings
- Customer-facing Wallet payment method
- Complete-wallet-payment validation
- Zero-remaining-total support foundation
- Balance-change revalidation
- Wallet payment session flag

## How it works

The dedicated Wallet payment method is available only when:

1. Customer is logged in.
2. Wallet payment is enabled.
3. Wallet has been applied to checkout.
4. Wallet covers the complete remaining order total.
5. The server confirms the wallet balance is still sufficient.

Example:

```text
Order total              ₹5,000
Wallet balance           ₹5,000
Wallet applied           ₹5,000
Remaining payment            ₹0
Payment method           Wallet
```

For:

```text
Order total              ₹5,000
Wallet balance           ₹3,000
Wallet applied           ₹3,000
Remaining payment        ₹2,000
```

the customer must select a normal payment method for the remaining ₹2,000. Wallet is not presented as the sole payment method.

## Security

The browser does not debit the wallet.

The payment controller only validates and stores the selected payment method.

The actual debit must be performed after the order ID exists through `WalletOrderService`.

## Journal 4

The payment block emits:

```javascript
ohbono:wallet-payment-selected
```

A Journal 4 adapter can listen for this event and invoke Journal's native payment-method selection/checkout refresh mechanism.

Do not hard-code private Journal functions without testing against the exact installed Journal 4 build.

## Next

Commit 0014 will add the order-created/payment-confirmed event adapter that connects the payment method to `WalletOrderService`, including zero-total wallet orders and cleanup after successful order creation.
