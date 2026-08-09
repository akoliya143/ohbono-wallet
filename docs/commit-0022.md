# Commit 0022 — Wallet Transaction Ledger

## Added

- Admin transaction ledger
- Customer filter
- Transaction type filter
- Credit/debit filter
- Order ID filter
- Date range filter
- Pagination
- Transaction detail page
- Customer reference
- Order reference
- Balance before/after
- Reference and comment display
- Transaction direction indicators

## Ledger example

```text
ID    Date       Customer       Type          Direction Amount  Before  After
--------------------------------------------------------------------------------
125   09/08      John Doe       order_payment Debit    ₹3,000  ₹6,000  ₹3,000
124   08/08      John Doe       admin_credit  Credit   ₹6,000  ₹0      ₹6,000
```

## Financial audit principle

Transactions are displayed from the immutable wallet ledger.

The transaction controller does not provide edit/delete operations.

This is intentional: financial transactions should be corrected using a
new compensating transaction rather than altering historical ledger rows.

## Next

Commit 0023 will add the customer-facing wallet checkout application flow:
apply wallet, remove wallet, available balance display, partial wallet
payment calculation and Journal 4-compatible checkout presentation.
