# Admin Menu / Permissions — 0108

Recommended admin navigation:

```text
OHBONO Wallet
├── Reconciliation
├── Refunds
└── Diagnostics
```

The implementation in this batch exposes menu metadata and permission
initialization without changing OpenCart core controllers.

Actual menu placement should be connected through the extension's event/menu
mechanism used by the target OpenCart 4.1.x installation.

## Permission principle

Use least privilege:

| Route | Access | Modify |
|---|---:|---:|
| Reconciliation | Yes | No |
| Refund | Yes | Yes |
| Diagnostics | Yes | No |

Only trusted administrators should receive refund modification permission.
