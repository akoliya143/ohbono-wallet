# Commit 0019 — Wallet Settings & Configuration

## Added

- Wallet enable/disable setting
- Checkout wallet enable/disable
- Minimum wallet usage
- Maximum wallet usage
- Wallet total sort order
- Customer history limit
- Permission validation
- Default settings
- Configuration-aware checkout total model

## Settings

```text
Wallet Status              Enabled
Wallet at Checkout         Enabled
Minimum Wallet Usage       ₹0
Maximum Wallet Usage       ₹0
Wallet Sort Order          5
History Limit              20
```

`0` for minimum/maximum means no restriction.

## Checkout behavior

The wallet total now uses:

```text
ohbono_wallet_status
ohbono_wallet_allow_checkout
ohbono_wallet_minimum_use
ohbono_wallet_maximum_use
```

instead of the old `total_wallet_*` configuration names.

## Important

Existing installations created with previous development batches should
migrate old setting names if those settings were already saved.

For a clean development installation, Commit 0019 uses the `ohbono_wallet_*`
settings as the canonical names.

## Next

Commit 0020 will add the installation/migration layer, extension event
registration cleanup and a single installer path so the complete wallet can
be installed without manually running individual SQL files.
