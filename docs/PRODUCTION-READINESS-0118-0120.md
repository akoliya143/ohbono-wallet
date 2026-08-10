# OHBONO Wallet — Production Readiness Gate

A green readiness result does not mean payment-provider certification has been
completed. It means the project's required internal controls and staging
scenarios have been explicitly verified.

## Required

- Database schema verified
- Admin permissions verified
- Wallet capture idempotency verified
- Refund/reversal verified
- Journal checkout verified
- Wallet-only staging order passed
- Partial-wallet staging order passed
- Failed external payment reconciliation passed
- Production backup completed

## Financial invariants

For every test order:

```text
Opening wallet balance
- wallet debits
+ wallet credits
= closing wallet balance
```

And:

```text
wallet ledger
+
order payment state
+
external payment state
=
same final outcome
```

## Do not release if

- Duplicate wallet debits occur.
- A refresh can duplicate a debit.
- A failed external payment leaves an unreconciled debit without an approved
  recovery path.
- Refunds modify/delete original ledger rows.
- Customer A can affect Customer B's order/wallet.
- Admin refund permission is too broad.
- Journal checkout bypasses the trusted server-side order flow.
