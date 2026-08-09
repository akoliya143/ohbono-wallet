# Commit 0032 — Installer, Uninstaller & Upgrade Hardening

## Added

- Idempotent wallet table creation
- Schema upgrade checks
- Missing-column protection
- Missing-index protection
- Default setting initialization
- Event upsert
- Extension version tracking
- Migration helper
- Safe uninstall policy

## Installation

The installer can be executed more than once without intentionally
duplicating:

- Wallet tables
- Wallet customer unique index
- Wallet order unique index
- Wallet events
- Default settings

Existing settings are preserved.

## Financial data protection

The uninstaller removes:

```text
OHBONO events
OHBONO configuration
```

but intentionally does NOT remove:

```text
wallet
wallet_transaction
wallet_order
```

This is deliberate.

Deleting a sexual-wellness customer's wallet balance or transaction ledger
during an ordinary extension uninstall would be unsafe.

If the business ever needs a permanent financial-data purge, that should be a
separate, explicitly approved administrative migration with backups.

## Upgrade version

The extension records:

```text
ohbono_wallet_version
```

The current commit version is:

```text
0032
```

Future commits can use the migration helper to apply only the required schema
changes.

## Important production note

The sample `uninstall.sql` uses `oc_` as the example prefix because SQL files
cannot safely know the runtime OpenCart DB prefix.

For production deployment, the PHP uninstaller is preferred because it uses:

```php
DB_PREFIX
```

## Next

Commit 0033 will add automated wallet integrity checks and reconciliation:
ledger-vs-balance verification, orphan transaction detection, duplicate order
detection and an admin health dashboard.
