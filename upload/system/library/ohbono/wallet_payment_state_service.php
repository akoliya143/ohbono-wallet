<?php
/**
 * OHBONO Wallet Payment State Reconciliation
 *
 * Lightweight payment-state classification used by order workflows.
 */
class OhbonoWalletPaymentStateService
{
    public function classify(
        float $order_total,
        float $wallet_paid,
        bool $secondary_payment_success
    ): string {
        $order_total = round(
            max(0.0, $order_total),
            4
        );

        $wallet_paid = round(
            max(0.0, $wallet_paid),
            4
        );

        if ($order_total <= 0) {
            return 'invalid';
        }

        if ($wallet_paid > $order_total) {
            return 'invalid';
        }

        $remaining = round(
            $order_total - $wallet_paid,
            4
        );

        if ($remaining <= 0.0001) {
            return 'paid_wallet';
        }

        if ($secondary_payment_success) {
            return 'paid_wallet_and_secondary';
        }

        if ($wallet_paid > 0) {
            return 'wallet_capture_requires_reconciliation';
        }

        return 'awaiting_payment';
    }
}
