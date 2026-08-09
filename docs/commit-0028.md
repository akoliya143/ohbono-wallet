# Commit 0028 — Customer Wallet UX & Pagination

## Added

- Customer wallet pagination
- Customer transaction detail page
- Total credited summary
- Total debited summary
- Transaction count
- Responsive wallet cards
- Responsive transaction table
- Transaction detail authorization
- Journal-friendly Bootstrap presentation

## Customer dashboard

The wallet page now shows:

```text
+----------------------------------+
| Available Wallet Balance        |
| ₹3,500.00                       |
+----------------------------------+

+----------------------------------+
| Wallet Summary                  |
| Credited | Debited | Transactions|
+----------------------------------+
```

## Pagination

Transactions are loaded in pages rather than loading the complete ledger.

The default history limit is controlled by:

```text
ohbono_wallet_history_limit
```

The controller caps the customer-facing page size at 50.

## Transaction details

Customer transaction detail URL:

```text
extension/ohbono/account/wallet.info
```

The model always requires both:

```text
customer_id
transaction_id
```

Therefore a customer cannot access another customer's transaction simply by
changing the transaction ID.

## Journal 4

The presentation uses standard Bootstrap/OpenCart classes and does not
replace Journal core templates.

The wallet account page can therefore be linked from Journal's customer
account menu.

## Next

Commit 0029 will add the customer wallet navigation/event integration and
OpenCart extension events needed to expose "My Wallet" consistently inside
the account area without modifying core OpenCart files.
