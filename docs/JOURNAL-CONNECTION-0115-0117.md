# Journal Checkout Connection — 0115–0117

## UI contract

The Journal checkout page should expose:

```html
<input type="hidden"
       data-ohbono-order-id
       value="ORDER_ID">

<input type="number"
       data-ohbono-wallet-amount
       value="0"
       min="0"
       step="0.01">
```

Load:

```text
catalog/view/javascript/ohbono/wallet-journal-checkout.js
```

Initialize:

```javascript
OhbonoWalletJournalCheckoutBridge.attach({
    endpoint: 'YOUR_CONFIRMED_CHECKOUT_ENDPOINT'
});
```

The bridge provides:

```javascript
OhbonoWalletJournalCheckout.getPayload()
```

## Important

The values are UX input only.

The server must:

1. Authenticate the customer.
2. Load the OpenCart order.
3. Confirm order ownership.
4. Re-read the order total.
5. Re-read wallet balance.
6. Lock the wallet.
7. Apply the wallet amount.
8. Record the transaction.
9. Return the resulting payment state.

## Journal-specific rule

Do not attach this bridge to every AJAX request globally. Attach it only to the
actual Journal checkout confirmation flow after the staging DOM/callback has
been confirmed.
