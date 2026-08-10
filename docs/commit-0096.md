# Commit 0096 — Order Wallet Payment Linkage

## Added

- Order-level wallet payment lookup
- Admin transaction lookup by order
- Durable order-to-wallet transaction relationship
- Static integration checks

## Git

```bash
git add .
git commit -m "feat(payment): link wallet capture to orders"

git add .
git commit -m "feat(payment): support partial wallet payments"

git add .
git commit -m "feat(admin): link wallet transactions to orders"

git push
```

## Critical integration rule

Wallet deduction must happen only after the OpenCart order exists and its final
server-side total is known.

For a partial payment:

```text
Order Total
   |
   +---- Wallet
   |
   +---- Remaining payment method
```

The wallet service only handles the wallet portion. It does not mark the
remaining payment as successful.

## Failure handling

The surrounding checkout/payment workflow must define what happens if the
remaining payment method fails after wallet capture. The safest production
design is to coordinate the wallet capture and final payment state through a
durable payment state machine, with an explicit reversal/refund path rather
than silently losing the wallet amount.
