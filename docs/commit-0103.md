# Commit 0103 — Wallet Security Hardening

## Added

- Strict amount normalization
- Reference validation
- Admin reason validation
- POST-only mutation checks
- Server-side trusted reference generation

Client-provided payment references are no longer used by the wallet capture
adapter.
