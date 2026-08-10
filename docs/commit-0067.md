# Commit 0067 — Wallet Email Queue

## Added

- Persistent wallet email queue
- Preference-aware queueing
- Transaction-linked idempotency
- Pending/sent/failed states
- Subject/message storage

Email queue creation happens after the wallet transaction is committed.
