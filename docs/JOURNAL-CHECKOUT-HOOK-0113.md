# Journal Checkout Hook — 0113

The Journal Theme 3.2 checkout should call the OHBONO presentation endpoint
after the checkout UI is rendered and after major checkout updates.

## Required UI contract

Add a wallet container:

```html
<div data-ohbono-wallet>
    <span data-ohbono-wallet-balance></span>
</div>
```

Load:

```text
catalog/view/javascript/ohbono/wallet-journal.js
```

Initialize:

```javascript
window.OhbonoWalletJournal.refresh({
    statusUrl: 'YOUR_WALLET_STATUS_URL'
});
```

## Important

`statusUrl` is a presentation endpoint.

It must never be used as proof that the customer has enough money at final
payment time.

## Journal integration rule

The actual Journal checkout confirm callback should eventually call the trusted
OpenCart order/payment flow. The OHBONO wallet service is invoked from that
trusted server-side flow, not directly from this UI script.
