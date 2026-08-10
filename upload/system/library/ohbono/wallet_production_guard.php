<?php
/**
 * OHBONO Wallet Production Guard
 *
 * Conservative server-side checks used before enabling financial mutations.
 * This guard does not replace OpenCart permissions or staging verification.
 */
class OhbonoWalletProductionGuard
{
    public function assertWritableEnvironment(): void
    {
        if (defined('OPENCART_CATALOG') &&
            OPENCART_CATALOG === 'production_disabled') {
            throw new \RuntimeException(
                'Wallet financial mutations are disabled.'
            );
        }
    }

    public function assertPositiveAmount(
        float $amount
    ): float {
        $amount = round($amount, 4);

        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                'Wallet amount must be greater than zero.'
            );
        }

        return $amount;
    }

    public function assertOrderTotal(
        float $order_total,
        float $wallet_amount
    ): void {
        $order_total = round(max(0, $order_total), 4);
        $wallet_amount = round(max(0, $wallet_amount), 4);

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
    }
}
