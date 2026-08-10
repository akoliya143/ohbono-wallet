<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletCheckout extends \Opencart\System\Engine\Controller
{
    public function availability(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'available' => false
            ]);
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_checkout'
        );

        $customer_id = (int)$this->customer->getId();

        $order_total = max(
            0.0,
            (float)($this->request->post['order_total'] ?? 0)
        );

        $requested_amount = max(
            0.0,
            (float)($this->request->post['amount'] ?? 0)
        );

        $usage =
            $this->model_extension_ohbono_module_wallet_checkout
                ->calculateUsage(
                    $customer_id,
                    $order_total,
                    $requested_amount
                );

        $this->json([
            'success' => true,
            'available' => $usage['wallet_balance'] > 0 &&
                $usage['usable_amount'] > 0,
            'usage' => $usage
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
