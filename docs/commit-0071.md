# Commit 0071 — Cron Entry Point

## Added

- OpenCart bootstrap-based cron entry
- Configurable batch size
- JSON execution summary
- Cron documentation

Example:

```bash
*/5 * * * * php /path/to/opencart/upload/cron/wallet_email_worker.php 20 >> /var/log/ohbono-wallet-email.log 2>&1
```

Use the actual OpenCart installation path and a PHP binary available to cron.
