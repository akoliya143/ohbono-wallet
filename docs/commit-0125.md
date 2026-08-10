# Commit 0125 — Atomic Capture and Refund Hardening

## Added

- Wallet row locking helper
- Explicit transaction begin/commit/rollback boundary
- Duplicate refund guard
- Cross-customer protection helper

The financial service should use these guards rather than duplicating ad-hoc
checks in controllers.
