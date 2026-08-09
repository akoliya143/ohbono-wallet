# Commit 0043 — Dynamic Checkout Wallet State

## Added

- Automatic wallet quote refresh
- Apply/remove wallet controls
- Current balance display
- Available wallet display
- Applied amount display
- Remaining payment display
- Public JavaScript refresh hook for Journal/custom checkout
- Stale reservation cleanup

## Checkout behavior

When cart totals change, the checkout can call:

```javascript
window.ohbonoWalletRefresh();
```

This is intended to run after:

- shipping changes
- coupon changes
- tax changes
- cart quantity changes
- other checkout total recalculations

## Important

The final server-side quote remains authoritative.

The browser display is informational only.
