<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Checkout;

class Wallet extends \Opencart\System\Engine\Controller
{
    /**
     * Returns the wallet amount attached to an order.
     *
     * This endpoint is informational and does not perform a debit/refund.
     */
    public function order(): void
    {
        $order_id = (int)($this->request->get['order_id'] ?? 0);

        if ($order_id <= 0 || !$this->customer->isLogged()) {
            $this->json([
                'success' => false
            ]);
            return;
        }

        $query = $this->db->query(
            "SELECT wo.amount, wo.status
             FROM `" . DB_PREFIX . "wallet_order` wo
             INNER JOIN `" . DB_PREFIX . "order` o
                ON (o.order_id = wo.order_id)
             WHERE wo.order_id = '" . (int)$order_id . "'
             AND o.customer_id = '" . (int)$this->customer->getId() . "'
             LIMIT 1"
        );

        if (!$query->num_rows) {
            $this->json([
                'success' => true,
                'wallet_amount' => 0.0,
                'status' => 0
            ]);
            return;
        }

        $this->json([
            'success' => true,
            'wallet_amount' => round((float)$query->row['amount'], 4),
            'status' => (int)$query->row['status']
        ]);
    }

    private function json(array $output): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($output));
    }
}
