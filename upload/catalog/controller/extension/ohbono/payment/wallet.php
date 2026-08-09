<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        $this->load->language('extension/ohbono/payment/wallet');
        $this->load->model('extension/ohbono/total/wallet');

        $data['name'] = 'wallet';
        $data['code'] = 'wallet';
        $data['title'] = $this->language->get('text_wallet');
        $data['description'] = $this->language->get('text_description');

        $data['balance'] = 0.0;
        $data['wallet_used'] = 0.0;

        if ($this->customer->isLogged()) {
            $customer_id = (int)$this->customer->getId();

            $data['balance'] = $this->model_extension_ohbono_total_wallet
                ->getAvailableBalance($customer_id);

            $data['wallet_used'] = $this->model_extension_ohbono_total_wallet
                ->getSessionWalletUse();
        }

        $data['text_available_balance'] = $this->language->get('text_available_balance');
        $data['text_wallet_used'] = $this->language->get('text_wallet_used');
        $data['text_remaining'] = $this->language->get('text_remaining');
        $data['text_apply'] = $this->language->get('text_apply');
        $data['text_remove'] = $this->language->get('text_remove');
        $data['text_amount'] = $this->language->get('text_amount');

        return $this->load->view(
            'extension/ohbono/payment/wallet',
            $data
        );
    }

    public function confirm(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json([
                'error' => 'Please login to use your wallet.'
            ]);
            return;
        }

        $this->load->model('extension/ohbono/total/wallet');

        $total = $this->model_extension_ohbono_total_wallet->getCurrentCartTotal();
        $requested = $this->model_extension_ohbono_total_wallet->getSessionWalletUse();

        $allowed = $this->model_extension_ohbono_total_wallet
            ->calculateAllowedWalletUse($requested, $total);

        if ($allowed <= 0) {
            $this->json([
                'error' => 'No wallet amount is available for this order.'
            ]);
            return;
        }

        $this->session->data['ohbono_wallet_use'] = $allowed;

        $this->json([
            'success' => true,
            'wallet_used' => $allowed,
            'remaining' => round(max(0, $total - $allowed), 4)
        ]);
    }

    /**
     * Finalize wallet debit after OpenCart has created the order.
     *
     * This method is intentionally not called from Apply Wallet.
     */
    public function finalize(int $order_id, int $customer_id, float $amount): array
    {
        if ($order_id <= 0 || $customer_id <= 0 || $amount <= 0) {
            return [
                'success' => false,
                'status' => 'invalid',
                'error' => 'Invalid wallet payment parameters.'
            ];
        }

        $this->load->library('ohbono/wallet_service');

        try {
            $transaction_id = $this->wallet_service->debitForOrder(
                $customer_id,
                $order_id,
                $amount
            );

            return [
                'success' => true,
                'status' => 'debited',
                'transaction_id' => $transaction_id
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear only the wallet checkout session values.
     *
     * This must be called after successful order finalization or when the
     * checkout is intentionally cancelled/reset.
     */
    public function clearCheckoutSession(): void
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
