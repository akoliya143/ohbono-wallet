# Commit 0044 — Wallet Removal & Reservation Expiry

## Added

- Remove wallet payment action
- Idempotent wallet restoration
- Reservation timestamp
- Stale reservation cleanup
- Configurable reservation TTL

Default TTL:

```text
1800 seconds
```

Minimum enforced TTL:

```text
300 seconds
```

## Behavior

If a customer applies wallet funds and then removes wallet payment:

```text
Wallet debit
   ↓
Remove wallet
   ↓
Restore transaction
   ↓
Wallet balance restored
```

The restore reference prevents a second restoration.
