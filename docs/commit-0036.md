# Commit 0036 — Wallet Refund Integration

## Added

- Central wallet refund service
- Order-linked refund credits
- Idempotent refund reference handling
- Duplicate refund protection
- Admin refund endpoint
- Integration endpoint for authenticated order/return workflows
- Refund reason requirement
- Wallet transaction linkage to original order

## Refund flow

```text
Order cancellation / return
        |
        v
Eligible wallet refund
        |
        v
Unique refund reference
        |
        v
OhbonoWalletRefund
        |
        +---- duplicate? ----> return existing transaction
        |
        v
WalletService.credit()
        |
        v
wallet_transaction
        |
        v
wallet_order (status = 2)
```

## Idempotency

Every refund must provide a unique reference such as:

```text
REFUND-ORDER-12345-RETURN-01
```

If the same order/reference is submitted again, the existing wallet
transaction is returned instead of creating another credit.

## Financial safety

Refunds are credits. They do not bypass the central wallet service.

The refund service records:

```text
type = order_refund
order_id = original order
reference = refund reference
```

## Important integration rule

Commit 0036 does not automatically refund every OpenCart order status change.

That would be unsafe because stores have different return/refund policies.

Instead, connect the refund service to the store's approved return/refund
workflow after eligibility and refund amount have been confirmed.

## Next

Commit 0037 will add customer-facing refund transaction messaging and order
wallet-refund history, plus admin refund visibility on the order/refund side.
