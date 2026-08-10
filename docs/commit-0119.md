# Commit 0119 — Duplicate Callback Protection

## Added

- Server-side callback reference lookup
- Existing wallet payment detection
- Explicit idempotency guard

The same trusted payment reference must never create a second wallet debit.
