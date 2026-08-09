# Commit 0035 — Admin Wallet Audit Log & CSV Export

## Added

- Admin audit-log screen
- Customer ID filter
- Admin user ID filter
- Credit/debit action filter
- Date-from filter
- Date-to filter
- Pagination
- CSV export
- Audit record display with before/after balances
- Reason and reference display

## Admin route

```text
extension/ohbono/module/wallet_audit
```

## CSV export

The export includes:

```text
Audit ID
Date
Customer ID
Admin User ID
Admin Name
Action
Amount
Balance Before
Balance After
Reference
Reason
IP Address
User Agent
Transaction ID
```

Export is limited to 10,000 records per request to avoid an uncontrolled
memory allocation in the admin request.

## Security

The audit page requires:

```text
access:
extension/ohbono/module/wallet_audit
```

It is read-only. No audit edit or delete endpoint is exposed.

## Recommended permissions

Only trusted accounting/administrator users should receive access to the
audit route because it contains operational and administrative metadata.

## Next

Commit 0036 will add wallet refund integration: automatic wallet refunds for
eligible order cancellations/returns, idempotent refund protection and
transaction linkage back to the original order payment.
