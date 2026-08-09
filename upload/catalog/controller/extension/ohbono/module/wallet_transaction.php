<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletTransaction extends \Opencart\System\Engine\Controller
{
    public function info(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_transaction'
        );

        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_login')
            ]);
            return;
        }

        $transaction_id = (int)(
            $this->request->get['transaction_id'] ?? 0
        );

        if ($transaction_id <= 0) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_transaction')
            ]);
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_transaction'
        );

        $transaction =
            $this->model_extension_ohbono_module_wallet_transaction
                ->getTransaction(
                    (int)$this->customer->getId(),
                    $transaction_id
                );

        if (!$transaction) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_not_found')
            ]);
            return;
        }

        $currency = $this->session->data['currency'];

        $this->json([
            'success' => true,
            'transaction' => [
                'transaction_id' =>
                    (int)$transaction['transaction_id'],
                'type' =>
                    (string)$transaction['type'],
                'direction' =>
                    (string)$transaction['direction'],
                'amount' => $this->currency->format(
                    (float)$transaction['amount'],
                    $currency
                ),
                'balance_before' => $this->currency->format(
                    (float)$transaction['balance_before'],
                    $currency
                ),
                'balance_after' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $currency
                ),
                'reference' =>
                    (string)$transaction['reference'],
                'order_id' =>
                    (int)$transaction['order_id'],
                'date_added' =>
                    (string)$transaction['date_added']
            ]
        ]);
    }

    private function json(array $data): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
