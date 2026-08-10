# Commit 0073 — Queue Recovery

## Added

- Automatic recovery of stale `processing` queue records
- Configurable stale threshold
- Safe return to `pending`
- Worker maintenance before processing

Default stale threshold: 15 minutes.
