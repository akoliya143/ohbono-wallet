<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletPaymentState extends \Opencart\System\Engine\Controller
{
    public function update(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => 'Customer login is required.'
            ]);
            return;
        }

        $order_id = (int)(
            $this->request->post['order_id'] ?? 0
        );

        $state = trim((string)(
            $this->request->post['state'] ?? ''
        ));

        $wallet_amount = round(
            max(
                0.0,
                (float)($this->request->post['wallet_amount'] ?? 0)
            ),
            4
        );

        $remaining_amount = round(
            max(
                0.0,
                (float)($this->request->post['remaining_amount'] ?? 0)
            ),
            4
        );

        if ($order_id <= 0 || $state === '') {
            $this->json([
                'success' => false,
                'error' => 'Order and state are required.'
            ]);
            return;
        }

        $this->load->model('checkout/order');

        $order =
            $this->model_checkout_order
                ->getOrder($order_id);

        if (!$order ||
            (int)$order['customer_id'] !==
                (int)$this->customer->getId()) {
            $this->json([
                'success' => false,
                'error' => 'Order not found.'
            ]);
            return;
        }

        try {
            $store =
                new \OhbonoWalletPaymentStateStore(
                    $this->db
                );

            $id = $store->ensureState(
                $order_id,
                (int)$this->customer->getId(),
                $state,
                $wallet_amount,
                $remaining_amount
            );

            $this->json([
                'success' => true,
                'wallet_payment_state_id' => $id
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
