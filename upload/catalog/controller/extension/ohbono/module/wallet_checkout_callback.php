<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

/**
 * Thin callback adapter.
 *
 * The installed checkout flow should call this only after it has created the
 * OpenCart order. The adapter validates ownership and then delegates to the
 * server-side wallet order service.
 */
class WalletCheckoutCallback extends \Opencart\System\Engine\Controller
{
    public function capture(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => 'Customer login is required.'
            ]);
            return;
        }

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'error' => 'POST request required.'
            ]);
            return;
        }

        $order_id = (int)(
            $this->request->post['order_id'] ?? 0
        );

        $wallet_amount =
            \OhbonoWalletSecurity::normalizeAmount(
                $this->request->post['amount'] ?? 0
            );

        if ($order_id <= 0 || $wallet_amount <= 0) {
            $this->json([
                'success' => false,
                'error' => 'Invalid checkout wallet request.'
            ]);
            return;
        }

        try {
            $this->load->model('checkout/order');

            $order =
                $this->model_checkout_order
                    ->getOrder($order_id);

            $guard =
                new \OhbonoWalletCheckoutCallbackGuard();

            $guard->validateOrder(
                $order,
                (int)$this->customer->getId()
            );

            $reference_service =
                new \OhbonoWalletReferenceService();

            $reference =
                $reference_service->payment(
                    $order_id
                );

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
                    (float)$order['total'],
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
