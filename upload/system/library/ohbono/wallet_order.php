<?php
/**
 * OHBONO Wallet order orchestration.
 *
 * This class is intended to be called after an OpenCart order has been
 * created and before the order/payment workflow is considered complete.
 */
class OhbonoWalletOrder
{
    private $registry;
    private $service;

    public function __construct($registry)
    {
        $this->registry = $registry;

        $registry->get('load')->library('ohbono/wallet_service');

        $this->service = $registry->get('wallet_service');
    }

    /**
     * Finalize wallet payment for an already-created order.
     *
     * Idempotency is provided by wallet_order.order_id.
     */
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
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process the wallet portion using the value stored in the checkout
     * session, then clear the session after successful finalization.
     */
    public function processFromSession(
        int $order_id,
        int $customer_id,
        float $final_order_total = 0.0
    ): array {
        $wallet_amount = round(
            (float)($this->registry->get('session')->data['ohbono_wallet_use'] ?? 0),
            4
        );

        if ($wallet_amount <= 0) {
            return [
                'success' => true,
                'status' => 'not_used',
                'wallet_amount' => 0.0,
                'transaction_id' => 0
            ];
        }

        /*
         * Never allow the session amount to exceed the final order total.
         * The wallet service performs the final balance check.
         */
        if ($final_order_total > 0) {
            $wallet_amount = min($wallet_amount, round($final_order_total, 4));
        }

        $result = $this->process(
            $order_id,
            $customer_id,
            $wallet_amount
        );

        if ($result['success']) {
            $this->registry->get('session')->data['ohbono_wallet_finalized'] = 1;
            $this->registry->get('session')->data['ohbono_wallet_order_id'] = $order_id;

            unset($this->registry->get('session')->data['ohbono_wallet_use']);

            $result['wallet_amount'] = $wallet_amount;
        }

        return $result;
    }

    public function clearSession(): void
    {
        unset(
            $this->registry->get('session')->data['ohbono_wallet_use'],
            $this->registry->get('session')->data['ohbono_wallet_order_id'],
            $this->registry->get('session')->data['ohbono_wallet_finalized']
        );
    }
}
