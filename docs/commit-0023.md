# Commit 0023 — Checkout Wallet Application

## Added

- Apply Wallet endpoint
- Remove Wallet endpoint
- Wallet information endpoint
- Available balance display
- Wallet amount input
- Partial wallet usage
- Maximum wallet usage enforcement
- Minimum wallet usage enforcement
- Cart total enforcement
- Checkout total integration
- Wallet payment presentation
- Journal-friendly custom checkout event

## Example

```text
Order total             ₹5,000
Wallet balance          ₹3,000

Customer applies        ₹3,000

Wallet                   -₹3,000
Remaining payment        ₹2,000
```

If the wallet balance is ₹8,000:

```text
Order total             ₹5,000
Wallet balance          ₹8,000

Wallet used             ₹5,000
Remaining payment           ₹0
```

The customer cannot apply more than:

```text
min(
    requested amount,
    current wallet balance,
    current payable cart total,
    configured maximum
)
```

## Important checkout behavior

The wallet amount is stored in:

```text
session.data['ohbono_wallet_use']
```

The actual financial debit is NOT performed when the customer clicks
"Apply Wallet".

The debit must happen only during the final order/payment workflow after
the order has been successfully created and the final payable amount has
been verified.

## Journal 4 compatibility

The wallet UI is deliberately isolated from Journal's private checkout
JavaScript.

After applying/removing wallet, the module dispatches:

```javascript
document.dispatchEvent(
    new CustomEvent('ohbonoWalletChanged', { detail: result })
);
```

A Journal 4 checkout integration can listen to this event and invoke the
appropriate Journal checkout refresh.

## Next

Commit 0024 will add the Journal 4 checkout refresh adapter and make the
wallet amount/remaining order total update automatically inside the Journal
checkout UI.
