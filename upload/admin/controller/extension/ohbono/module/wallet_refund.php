<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletRefund extends \Opencart\System\Engine\Controller
{
    public function refund(): void
    {
        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_refund'
        )) {
            $this->json([
                'success' => false,
                'error' => 'Permission denied.'
            ]);
            return;
        }

        $customer_id = (int)(
            $this->request->post['customer_id'] ?? 0
        );

        $order_id = (int)(
            $this->request->post['order_id'] ?? 0
        );

        $reference = trim((string)(
            $this->request->post['reference'] ?? ''
        ));

        $reason = trim((string)(
            $this->request->post['reason'] ?? ''
        ));

        if ($customer_id <= 0 ||
            $order_id <= 0 ||
            $reference === '' ||
            $reason === '') {
            $this->json([
                'success' => false,
                'error' => 'Customer, order, reference and reason are required.'
            ]);
            return;
        }

        try {
            $reversal =
                new \OhbonoWalletReversalService(
                    $this->db
                );

            $refund =
                new \OhbonoWalletRefundService(
                    $this->db,
                    $reversal
                );

            $transaction_id =
                $refund->refundOrderWalletPayment(
                    $customer_id,
                    $order_id,
                    $reference,
                    $reason,
                    (int)$this->user->getId()
                );

            $this->json([
                'success' => true,
                'transaction_id' => $transaction_id
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
