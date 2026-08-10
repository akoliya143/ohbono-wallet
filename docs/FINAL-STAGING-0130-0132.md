# OHBONO Wallet — Final Staging Execution

## Purpose

This package is the final evidence layer before production release.

It does not fabricate or infer staging results.

## Required scenarios

| Scenario | Required result |
|---|---|
| Wallet-only checkout | PASS |
| Partial wallet + external success | PASS |
| Partial wallet + external failure | PASS |
| Insufficient balance | PASS |
| Wallet amount above order total | PASS |
| Duplicate callback | PASS |
| Browser refresh | PASS |
| Refund after paid order | PASS |
| Reversal after failed external payment | PASS |
| Cross-customer protection | PASS |
| Ledger reconciliation | PASS |
| Journal checkout | PASS |

## Evidence to capture

For each scenario record:

- Staging order ID
- Wallet transaction ID
- Payment reference
- Before balance
- Wallet debit/credit
- After balance
- Order status
- External payment result
- Final payment state
- Notes/screenshots where appropriate

## Financial verification

For every successful wallet mutation:

```text
Opening balance
    - debit
    + credit
    = closing balance
```

For every completed order:

```text
Order total
=
Wallet contribution
+
External contribution
-
Approved reversal
```

## Release decision

Release is allowed only when every required scenario is explicitly verified
as PASS.

A missing, failed or unrun scenario keeps the release blocked.
