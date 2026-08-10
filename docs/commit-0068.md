# Commit 0068 — Wallet Email Worker & Retry

## Added

- Cron-friendly email worker
- Processing state
- Attempt counter
- Exponential retry delay
- Maximum of five attempts
- Permanent failed state
- Error recording

Example cron:

```bash
*/5 * * * * php /path/to/opencart/extension/ohbono/cron/wallet_email_worker.php
```

The exact cron entry should use the real OpenCart installation path.
