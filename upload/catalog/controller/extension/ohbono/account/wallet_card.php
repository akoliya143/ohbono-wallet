<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Account;

class WalletCard extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        if (!$this->customer->isLogged()) {
            return '';
        }

        $this->load->language('extension/ohbono/account/wallet_card');
        $this->load->model('extension/ohbono/account/wallet');

        $currency = $this->session->data['currency'] ?? $this->config->get('config_currency');

        $data['text_balance'] = $this->language->get('text_balance');
        $data['button_view_wallet'] = $this->language->get('button_view_wallet');

        $data['balance'] = $this->currency->format(
            $this->model_extension_ohbono_account_wallet->getBalance(
                (int)$this->customer->getId()
            ),
            $currency
        );

        $data['wallet_url'] = $this->url->link(
            'extension/ohbono/account/wallet',
            'language=' . $this->config->get('config_language')
        );

        return $this->load->view('extension/ohbono/account/wallet_card', $data);
    }
}
