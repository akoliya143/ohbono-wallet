# Commit 0034 — Wallet Audit Logging & Correction Safeguards

## Added

- Immutable wallet audit records
- Required manual adjustment reason
- Before/after balance capture
- Admin user identity
- Admin IP address
- Admin user-agent metadata
- Wallet transaction reference
- Audit lookup by customer
- Manual debit balance pre-check

## Audit record

Each manual adjustment records:

```text
Customer
Transaction
Admin user
Action
Amount
Balance before
Balance after
Reference
Reason
IP address
User agent
Timestamp
```

## Financial safety

The admin adjustment still goes through:

```text
OhbonoWalletService
```

The audit layer does not directly modify the wallet balance.

## Reason requirement

A manual credit/debit cannot be submitted without a reason.

Examples:

```text
Customer refund approved by support ticket #1234
Promotional wallet credit approved by marketing
Correction for duplicate wallet debit
```

## Important implementation note

The existing `wallet_customer_info.twig` is intentionally represented as a
small commit patch in this ZIP. Merge its required `reason` field into the
existing Commit 0030/0031 admin template rather than replacing the entire
template blindly.

Likewise, merge the new `processAdjustment()` and `adjust()` signatures into
the existing Commit 0030 implementation.

## Audit immutability

The audit library exposes only INSERT behavior. There is no update/delete
API in the extension layer.

For stronger production enforcement, database permissions should also prevent
the web application's database user from updating or deleting audit records.

## Next

Commit 0035 will add the admin audit-log screen with filters for customer,
admin, action, date range and transaction, plus CSV export for accounting
review.
