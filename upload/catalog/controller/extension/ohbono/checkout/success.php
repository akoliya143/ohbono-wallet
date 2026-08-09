<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Checkout;

/**
 * OHBONO checkout success integration.
 *
 * This controller provides a safe, explicit integration endpoint. It does
 * not debit merely because the success page is displayed.
 *
 * The recommended integration is to call this logic from the store's final
 * order-created/payment-success callback, where the order ID and customer ID
 * are authoritative.
 */
class Success extends \Opencart\System\Engine\Controller
{
    public function finalize(): void
    {
        $order_id = (int)($this->request->post['order_id'] ?? 0);
        $customer_id = (int)($this->request->post['customer_id'] ?? 0);

        if (!$order_id || !$customer_id) {
            $this->json([
                'success' => false,
                'error' => 'Order and customer are required.'
            ]);
            return;
        }

        if (!$this->customer->isLogged() ||
            (int)$this->customer->getId() !== $customer_id) {
            $this->json([
                'success' => false,
                'error' => 'Customer verification failed.'
            ]);
            return;
        }

        $wallet_amount = round(
            (float)($this->session->data['ohbono_wallet_use'] ?? 0),
            4
        );

        if ($wallet_amount <= 0) {
            $this->clearWalletSession();

            $this->json([
                'success' => true,
                'status' => 'not_used'
            ]);

            return;
        }

        $this->load->library('ohbono/wallet_order');

        $result = $this->wallet_order->processFromSession(
            $order_id,
            $customer_id
        );

        if (!$result['success']) {
            $this->json([
                'success' => false,
                'status' => 'wallet_debit_failed',
                'error' => $result['message'] ?? 'Wallet debit failed.'
            ]);

            return;
        }

        $this->json($result);
    }

    private function clearWalletSession(): void
    {
        unset(
            $this->session->data['ohbono_wallet_use'],
            $this->session->data['ohbono_wallet_order_id'],
            $this->session->data['ohbono_wallet_finalized']
        );
    }

    private function json(array $data): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
