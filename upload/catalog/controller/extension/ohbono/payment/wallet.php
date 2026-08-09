<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');

        $data['name'] = 'wallet';
        $data['code'] = 'wallet';
        $data['title'] = $this->language->get('text_wallet');
        $data['description'] = $this->language->get('text_description');

        $this->load->model('extension/ohbono/total/wallet');

        $data['balance'] = 0.0;
        $data['wallet_used'] = $this->model_extension_ohbono_total_wallet->getSessionWalletUse();

        if ($this->customer->isLogged()) {
            $data['balance'] = $this->model_extension_ohbono_total_wallet->getAvailableBalance(
                (int)$this->customer->getId()
            );
        }

        $data['currency'] = $this->session->data['currency'] ?? $this->config->get('config_currency');

        return $this->load->view('extension/ohbono/payment/wallet', $data);
    }

    public function confirm(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json(['error' => 'Please login to use your wallet.']);
            return;
        }

        $this->load->model('extension/ohbono/total/wallet');

        $cart_total = $this->model_extension_ohbono_total_wallet->getCurrentCartTotal();
        $balance = $this->model_extension_ohbono_total_wallet->getAvailableBalance(
            (int)$this->customer->getId()
        );
        $used = $this->model_extension_ohbono_total_wallet->getSessionWalletUse();

        $allowed = $this->model_extension_ohbono_total_wallet->calculateAllowedWalletUse(
            $used,
            $cart_total
        );

        if ($allowed <= 0) {
            $this->json([
                'error' => 'No wallet amount is available for this order.'
            ]);
            return;
        }

        $this->json([
            'success' => true,
            'wallet_used' => $allowed,
            'wallet_balance' => $balance,
            'remaining' => round(max(0, $cart_total - $allowed), 4)
        ]);
    }

    private function json(array $data): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
