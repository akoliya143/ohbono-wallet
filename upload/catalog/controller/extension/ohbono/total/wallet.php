<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Total;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        if (!$this->customer->isLogged()) {
            return '';
        }

        if (!(int)$this->config->get('total_wallet_status') ||
            !(int)$this->config->get('total_wallet_allow_checkout')) {
            return '';
        }

        $this->load->language('extension/ohbono/total/wallet');
        $this->load->model('extension/ohbono/total/wallet');

        $balance = $this->model_extension_ohbono_total_wallet->getAvailableBalance(
            (int)$this->customer->getId()
        );

        if ($balance <= 0) {
            return '';
        }

        $currency = $this->session->data['currency'] ?? $this->config->get('config_currency');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_available'] = $this->language->get('text_available');
        $data['text_amount'] = $this->language->get('text_amount');
        $data['text_apply'] = $this->language->get('text_apply');
        $data['text_remove'] = $this->language->get('text_remove');
        $data['text_balance'] = $this->currency->format($balance, $currency);

        $data['wallet_use'] = (float)($this->session->data['ohbono_wallet_use'] ?? 0);

        $data['apply'] = $this->url->link(
            'extension/ohbono/total/wallet.apply',
            'language=' . $this->config->get('config_language')
        );

        $data['remove'] = $this->url->link(
            'extension/ohbono/total/wallet.remove',
            'language=' . $this->config->get('config_language')
        );

        return $this->load->view('extension/ohbono/total/wallet', $data);
    }

    public function apply(): void
    {
        $this->load->language('extension/ohbono/total/wallet');

        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_login')
            ]);

            return;
        }

        if (!(int)$this->config->get('total_wallet_status') ||
            !(int)$this->config->get('total_wallet_allow_checkout')) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_disabled')
            ]);

            return;
        }

        $amount = (float)($this->request->post['amount'] ?? 0);

        if ($amount <= 0) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_amount')
            ]);

            return;
        }

        $this->load->model('extension/ohbono/total/wallet');

        $cart_total = $this->model_extension_ohbono_total_wallet->getCurrentCartTotal();
        $allowed = $this->model_extension_ohbono_total_wallet->calculateAllowedWalletUse(
            $amount,
            $cart_total
        );

        if ($allowed <= 0) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_amount')
            ]);

            return;
        }

        $this->session->data['ohbono_wallet_use'] = $allowed;

        $currency = $this->session->data['currency'] ?? $this->config->get('config_currency');
        $remaining = max(0, $cart_total - $allowed);

        $this->json([
            'success' => true,
            'wallet_use' => $allowed,
            'formatted_wallet_use' => $this->currency->format($allowed, $currency),
            'remaining' => $remaining,
            'formatted_remaining' => $this->currency->format($remaining, $currency)
        ]);
    }

    public function remove(): void
    {
        unset($this->session->data['ohbono_wallet_use']);

        $this->json(['success' => true]);
    }

    public function getTotal(): void
    {
        $this->load->model('extension/ohbono/total/wallet');

        $this->json([
            'success' => true,
            'wallet_use' => $this->model_extension_ohbono_total_wallet->getSessionWalletUse()
        ]);
    }

    private function json(array $output): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(
            json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
