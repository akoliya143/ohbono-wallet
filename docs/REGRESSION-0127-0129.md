# OHBONO Wallet — Regression and Deployment Validation

This batch is intentionally conservative. It does not assume that a staging
failure occurred and does not mark any real checkout as passed.

## 0127 — Regression Suite

Critical invariants are checked again after the final hardening work:

- amount normalization
- order total limit
- customer/order ownership
- ledger arithmetic
- payment-reference idempotency

## 0128 — Order State Protection

Wallet capture and refund should not operate on arbitrary order states.

The actual allowed status IDs must be configured for the target OpenCart
installation. They are deliberately not hard-coded as production truth.

## 0129 — Deployment Validation

Before uploading the extension, the deployment validator confirms the critical
wallet files are present.

It does not:

- modify the database
- enable wallet payments
- register events
- change order statuses
- alter balances
