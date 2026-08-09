<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletCustomer extends \Opencart\System\Engine\Controller
{
    public function credit(): void
    {
        $this->processAdjustment('credit');
    }

    public function debit(): void
    {
        $this->processAdjustment('debit');
    }

    private function processAdjustment(string $direction): void
    {
        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_customer'
        )) {
            $this->json([
                'success' => false,
                'error' => 'Permission denied.'
            ]);
            return;
        }

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);
        $amount = round((float)($this->request->post['amount'] ?? 0), 4);
        $reference = trim((string)($this->request->post['reference'] ?? ''));
        $comment = trim((string)($this->request->post['comment'] ?? ''));
        $reason = trim((string)($this->request->post['reason'] ?? ''));

        if ($customer_id <= 0 || $amount <= 0) {
            $this->json([
                'success' => false,
                'error' => 'Customer and a positive amount are required.'
            ]);
            return;
        }

        if ($amount > 100000000) {
            $this->json([
                'success' => false,
                'error' => 'The amount is above the permitted limit.'
            ]);
            return;
        }

        if ($reason === '') {
            $this->json([
                'success' => false,
                'error' => 'A correction reason is required.'
            ]);
            return;
        }

        if (mb_strlen($reason) > 500) {
            $this->json([
                'success' => false,
                'error' => 'The correction reason is too long.'
            ]);
            return;
        }

        $this->load->model('extension/ohbono/module/wallet_customer');

        try {
            $result = $this->model_extension_ohbono_module_wallet_customer
                ->adjust(
                    $customer_id,
                    $direction,
                    $amount,
                    $reference,
                    $comment,
                    $reason,
                    (int)$this->user->getId()
                );

            $this->json([
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'balance_before' => $result['balance_before'],
                'balance' => $result['balance'],
                'reason' => $reason
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
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
