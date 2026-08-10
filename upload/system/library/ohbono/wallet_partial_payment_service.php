<?php
/**
 * OHBONO Partial Wallet Payment Service
 *
 * Calculates the amount that must be covered by the remaining payment
 * method. It intentionally does not charge the secondary payment method.
 */
class OhbonoWalletPartialPaymentService
{
    public function calculate(
        float $order_total,
        float $wallet_amount
    ): array {
        $order_total = round(
            max(0.0, $order_total),
            4
        );

        $wallet_amount = round(
            max(0.0, $wallet_amount),
            4
        );

        if ($order_total <= 0) {
            throw new \InvalidArgumentException(
                'Order total must be greater than zero.'
            );
        }

        if ($wallet_amount > $order_total) {
            throw new \InvalidArgumentException(
                'Wallet amount cannot exceed order total.'
            );
        }

        $remaining = round(
            $order_total - $wallet_amount,
            4
        );

        return [
            'order_total' => $order_total,
            'wallet_amount' => $wallet_amount,
            'remaining_amount' => $remaining,
            'fully_paid_by_wallet' =>
                $remaining <= 0.0001
        ];
    }
}
