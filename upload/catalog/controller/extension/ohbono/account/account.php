<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Account;

class Account extends \Opencart\System\Engine\Controller
{
    public function walletLink(): string
    {
        if (!$this->customer->isLogged()) {
            return '';
        }

        $this->load->language('extension/ohbono/account/account');

        $data['wallet'] = $this->url->link(
            'extension/ohbono/account/wallet',
            'language=' . $this->config->get('config_language')
        );

        $data['text_wallet'] = $this->language->get('text_wallet');

        return $this->load->view(
            'extension/ohbono/account/wallet_link',
            $data
        );
    }
}
