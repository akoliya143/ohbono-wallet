<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletPartialPayment extends \Opencart\System\Engine\Controller
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

        $order_total = round(
            max(
                0.0,
                (float)($this->request->post['order_total'] ?? 0)
            ),
            4
        );

        $wallet_amount = round(
            max(
                0.0,
                (float)($this->request->post['amount'] ?? 0)
            ),
            4
        );

        try {
            $service =
                new \OhbonoWalletPartialPaymentService();

            $result =
                $service->calculate(
                    $order_total,
                    $wallet_amount
                );

            $this->json([
                'success' => true,
                'result' => $result
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
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
