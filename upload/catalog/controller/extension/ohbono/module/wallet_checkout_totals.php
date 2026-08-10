<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

/**
 * Checkout totals calculator.
 *
 * This is intentionally non-mutating. It calculates a proposed wallet
 * contribution and leaves the final deduction to the order/payment layer.
 */
class WalletCheckoutTotals extends \Opencart\System\Engine\Controller
{
    public function calculate(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => 'Customer login is required.'
            ]);
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_checkout'
        );

        $order_total = max(
            0.0,
            (float)($this->request->post['order_total'] ?? 0)
        );

        $requested_amount = max(
            0.0,
            (float)($this->request->post['amount'] ?? 0)
        );

        if ($order_total <= 0.0) {
            $this->json([
                'success' => false,
                'error' => 'Invalid order total.'
            ]);
            return;
        }

        $usage =
            $this->model_extension_ohbono_module_wallet_checkout
                ->calculateUsage(
                    (int)$this->customer->getId(),
                    $order_total,
                    $requested_amount
                );

        $this->json([
            'success' => true,
            'wallet_amount' => $usage['usable_amount'],
            'remaining_total' => $usage['remaining_total']
        ]);
    }

    private function json(array $data): void
    {
        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode($data)
        );
    }
}
