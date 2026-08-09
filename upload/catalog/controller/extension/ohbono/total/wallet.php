<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Total;

use Opencart\System\Library\Ohbono\WalletFactory;

class Wallet extends \Opencart\System\Engine\Controller
{
    /**
     * Return the generic wallet checkout block.
     *
     * Journal 4 can later render this block through its own checkout
     * integration without changing the wallet calculation engine.
     */
    public function index(): string
    {
        if (!$this->customer->isLogged()) {
            return '';
        }

        if (!(int)$this->config->get('total_wallet_status')) {
            return '';
        }

        if (!(int)$this->config->get('total_wallet_allow_checkout')) {
            return '';
        }

        $this->load->language('extension/ohbono/total/wallet');

        $this->load->library('ohbono/WalletFactory');

        $factory = new WalletFactory($this->registry);
        $service = $factory->service();

        if (!$service->isEnabled()) {
            return '';
        }

        $balance = $service->getBalance((int)$this->customer->getId());

        if ($balance <= 0) {
            return '';
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_available'] = $this->language->get('text_available');
        $data['text_amount'] = $this->language->get('text_amount');
        $data['text_apply'] = $this->language->get('text_apply');
        $data['text_remove'] = $this->language->get('text_remove');
        $data['text_balance'] = $this->currency->format(
            $balance,
            $this->session->data['currency'] ?? $this->config->get('config_currency')
        );

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

    /**
     * Apply a wallet amount to the current checkout session.
     *
     * This does not debit the wallet.
     */
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

        try {
            $this->load->model('extension/ohbono/total/wallet');

            $cart_total = $this->getCurrentCartTotal();

            $allowed = $this->model_extension_ohbono_total_wallet
                ->calculateAllowedWalletUse($amount, $cart_total);

            if ($allowed <= 0) {
                $this->json([
                    'success' => false,
                    'error' => $this->language->get('error_amount')
                ]);

                return;
            }

            $this->session->data['ohbono_wallet_use'] = $allowed;

            $this->json([
                'success' => true,
                'wallet_use' => $allowed,
                'formatted_wallet_use' => $this->currency->format(
                    $allowed,
                    $this->session->data['currency'] ?? $this->config->get('config_currency')
                ),
                'remaining' => max(0, $cart_total - $allowed),
                'formatted_remaining' => $this->currency->format(
                    max(0, $cart_total - $allowed),
                    $this->session->data['currency'] ?? $this->config->get('config_currency')
                )
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_operation')
            ]);
        }
    }

    /**
     * Remove wallet usage from the checkout session.
     */
    public function remove(): void
    {
        unset($this->session->data['ohbono_wallet_use']);

        $this->json([
            'success' => true
        ]);
    }

    private function getCurrentCartTotal(): float
    {
        $this->load->model('checkout/cart');

        $totals = [];
        $taxes = [];
        $total = 0.0;

        ($this->model_checkout_cart->getTotals)($totals, $taxes, $total);

        return round($total, 4);
    }

    private function json(array $output): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($output));
    }
}
