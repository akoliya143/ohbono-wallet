<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Event;

class Refund extends \Opencart\System\Engine\Controller
{
    /**
     * Explicit refund endpoint for integrations.
     *
     * This controller is intentionally not exposed as a public customer
     * endpoint. Call it from an authenticated admin/order-return integration
     * or invoke the refund library from the appropriate OpenCart event.
     */
    public function process(): void
    {
        $this->response->addHeader('Content-Type: application/json');

        $this->load->library('ohbono/wallet_service');
        $this->load->library('ohbono/refund');

        $order_id = (int)($this->request->post['order_id'] ?? 0);
        $customer_id = (int)($this->request->post['customer_id'] ?? 0);
        $amount = round(
            (float)($this->request->post['amount'] ?? 0),
            4
        );
        $reference = trim(
            (string)($this->request->post['reference'] ?? '')
        );
        $reason = trim(
            (string)($this->request->post['reason'] ?? '')
        );

        try {
            $result = $this->wallet_refund->refund(
                $order_id,
                $customer_id,
                $amount,
                $reference,
                $reason
            );

            $this->response->setOutput(
                json_encode([
                    'success' => true,
                    'already_refunded' => $result['already_refunded'],
                    'transaction_id' => $result['transaction_id'],
                    'amount' => $result['amount']
                ])
            );
        } catch (\Throwable $e) {
            $this->response->setOutput(
                json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ])
            );
        }
    }
}
