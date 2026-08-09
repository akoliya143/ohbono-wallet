<?php
/**
 * OHBONO Wallet order orchestration helper.
 *
 * This helper is deliberately separate from checkout presentation.
 * It should be called only after OpenCart has created an order.
 */

class OhbonoWalletOrder
{
    private $registry;
    private $service;

    public function __construct($registry)
    {
        $this->registry = $registry;

        if (!$registry->has('load')) {
            throw new RuntimeException('OpenCart loader is unavailable.');
        }

        $registry->get('load')->library('ohbono/wallet_service');

        $this->service = $registry->get('wallet_service');
    }

    public function process(
        int $order_id,
        int $customer_id,
        float $wallet_amount
    ): array {
        $wallet_amount = round($wallet_amount, 4);

        if ($order_id <= 0 || $customer_id <= 0 || $wallet_amount <= 0) {
            return [
                'success' => false,
                'status' => 'invalid',
                'message' => 'Invalid wallet order parameters.'
            ];
        }

        try {
            $transaction_id = $this->service->debitForOrder(
                $customer_id,
                $order_id,
                $wallet_amount
            );

            return [
                'success' => true,
                'status' => 'debited',
                'transaction_id' => $transaction_id
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }
}
