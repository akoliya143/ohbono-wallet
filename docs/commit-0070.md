# Commit 0070 — Production Queue Worker

## Added

- CLI-oriented wallet email worker
- Queue claiming with database locking
- Processing state
- Bounded batch size
- Safe concurrent-worker behavior

Only pending records whose `available_at` has elapsed can be claimed.
