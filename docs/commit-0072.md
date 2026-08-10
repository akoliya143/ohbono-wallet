# Commit 0072 — Admin Retry Controls

## Added

- Failed queue retry action
- Modify-permission protection
- Admin retry button
- Queue status visibility
- Retry clears the previous error and makes the item immediately available

## Git

```bash
git add .
git commit -m "feat(wallet): add locked email queue worker"

git add .
git commit -m "feat(wallet): add cron email worker entry point"

git add .
git commit -m "feat(admin): add failed wallet email retry"

git push
```

## Production note

Run only one cron invocation at a time per server, or keep the worker's
database locking enabled when multiple workers are intentionally deployed.
