# Commit 0016 — Customer Wallet Account & History

## Added

- Customer wallet account page
- Current wallet balance
- Wallet transaction history
- Credit/debit separation
- Running balance display
- Order reference display
- Pagination
- Customer-safe transaction queries
- Reusable account wallet card

## Customer experience

The customer can see:

```text
My Wallet

Available Balance
₹3,000

Wallet History

Date       Description       Order     Credit    Debit    Balance
------------------------------------------------------------------
08/08      Wallet Credit                +₹5,000            ₹5,000
09/08      Order Payment      #1025                -₹3,000 ₹2,000
```

## Security

The account page always uses the logged-in customer's ID from the server-side
customer session.

A customer cannot request another customer's wallet history by changing a
customer_id URL parameter.

## Journal 4

The wallet account page is implemented as a standalone OpenCart account
route. The reusable `wallet_card` component can be placed into the Journal 4
customer account layout using Journal's layout/module assignment system.

The core wallet code does not depend on Journal's private JavaScript.

## Next

Commit 0017 will add the Admin Wallet Dashboard with total wallet liability,
customer count, credits, debits, recent transactions and quick management
links.
