<?php
/**
 * OHBONO Wallet Payment Validator
 *
 * Revalidates the trusted checkout state immediately before capture.
 */
class OhbonoWalletPaymentValidator
{
    public function validate(
        float $order_total,
        float $wallet_amount
    ): void {
        $order_total = round(
            max(0.0, $order_total),
            4
        );

        $wallet_amount = round(
            max(0.0, $wallet_amount),
            4
        );

        if ($order_total <= 0) {
            throw new \RuntimeException(
                'Invalid order total.'
            );
        }

        if ($wallet_amount <= 0) {
            throw new \RuntimeException(
                'Invalid wallet payment amount.'
            );
        }

        if ($wallet_amount > $order_total) {
            throw new \RuntimeException(
                'Wallet payment exceeds order total.'
            );
        }
    }
}
