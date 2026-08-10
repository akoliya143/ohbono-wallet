<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletOrderPayment extends \Opencart\System\Engine\Controller
{
    /**
     * Order-level wallet capture adapter.
     *
     * The preferred integration is to call the service directly from the
     * trusted checkout/payment flow. This endpoint remains intentionally
     * thin and performs no client-side authorization.
     */
    public function capture(): void
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

        $wallet_amount = round(
            max(
                0.0,
                (float)($this->request->post['amount'] ?? 0)
            ),
            4
        );

        $reference = trim((string)(
            $this->request->post['reference'] ?? ''
        ));

        if ($order_id <= 0 ||
            $wallet_amount <= 0 ||
            $reference === '') {
            $this->json([
                'success' => false,
                'error' => 'Incomplete wallet order payment request.'
            ]);
            return;
        }

        try {
            /*
             * The OpenCart order model is the source of truth for the total.
             */
            $this->load->model('checkout/order');

            $order_info =
                $this->model_checkout_order
                    ->getOrder($order_id);

            if (!$order_info ||
                (int)$order_info['customer_id'] !==
                    (int)$this->customer->getId()) {
                throw new \RuntimeException(
                    'Order not found.'
                );
            }

            $wallet_payment =
                new \OhbonoWalletPaymentService(
                    $this->db
                );

            $service =
                new \OhbonoWalletOrderPaymentService(
                    $this->db,
                    $wallet_payment
                );

            $result =
                $service->captureForOrder(
                    (int)$this->customer->getId(),
                    $order_id,
                    (float)$order_info['total'],
                    $wallet_amount,
                    $reference
                );

            $this->json([
                'success' => true,
                'transaction_id' =>
                    $result['transaction_id'],
                'wallet_amount' =>
                    $result['wallet_amount'],
                'remaining_amount' =>
                    $result['remaining_amount'],
                'idempotent' =>
                    $result['idempotent']
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
