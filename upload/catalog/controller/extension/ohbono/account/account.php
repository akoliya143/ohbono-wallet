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

    /**
     * Journal/OpenCart account-menu event helper.
     *
     * Returns a ready-to-render menu item without replacing core account
     * templates. The event handler can append this to an existing menu.
     */
    public function walletMenuItem(): array
    {
        if (!$this->customer->isLogged()) {
            return [];
        }

        $this->load->language('extension/ohbono/account/account');

        return [
            'name' => $this->language->get('text_wallet'),
            'href' => $this->url->link(
                'extension/ohbono/account/wallet',
                'language=' . $this->config->get('config_language')
            ),
            'icon' => 'fa-solid fa-wallet',
            'route' => 'extension/ohbono/account/wallet',
            'sort_order' => 20
        ];
    }
}
