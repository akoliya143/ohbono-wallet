# Commit 0027 — Customer Wallet Account & History

## Added

- Customer wallet account page
- Current wallet balance
- Customer transaction history
- Credit/debit indicators
- Order references
- Running balance after each transaction
- Account-page wallet link component

## Customer URL

```text
index.php?route=extension/ohbono/account/wallet
```

The controller also includes the OpenCart `language` parameter.

## Customer history

The customer sees:

```text
Date
Type
Credit / Debit
Reference
Order
Amount
Balance after transaction
```

Only transactions belonging to the currently logged-in customer are queried.

The transaction `comment` and administrative metadata are intentionally not
exposed on the customer list.

## Security

The model always requires the authenticated customer's ID.

A customer cannot request another customer's transaction by changing an ID
in the URL because the controller obtains the ID from:

```php
$this->customer->getId()
```

## Account integration

The package provides:

```text
extension/ohbono/account/account.walletLink
```

which can be included by an account-page customization without modifying
OpenCart core account controllers.

For Journal 4, the link can also be added through the Journal account-menu
customization.

## Next

Commit 0028 will add wallet customer pagination, transaction detail pages,
customer-side wallet usage summaries and a responsive Journal-compatible
account presentation.
