# Commit 0097 — Wallet Reversal Primitive

## Added

- Compensating wallet credit
- Original transaction immutability
- Original debit validation
- Wallet row locking
- Atomic reversal transaction
- Idempotent reversal reference
- Audit record for reversal

A reversal creates a new transaction rather than modifying the original debit.
