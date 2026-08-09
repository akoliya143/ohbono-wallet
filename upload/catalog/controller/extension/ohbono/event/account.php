<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Event;

class Account extends \Opencart\System\Engine\Controller
{
    public function links(
        string &$route,
        array &$args,
        mixed &$output
    ): void {
        if (!$this->customer->isLogged()) {
            return;
        }

        if (!$this->config->get('ohbono_wallet_status')) {
            return;
        }

        $wallet_url = $this->url->link(
            'extension/ohbono/module/wallet'
        );

        $history_url = $this->url->link(
            'extension/ohbono/module/wallet_history'
        );

        $link = [
            'name' => 'OHBONO Wallet',
            'href' => $wallet_url,
            'sort_order' => 25
        ];

        $history = [
            'name' => 'Wallet History',
            'href' => $history_url,
            'sort_order' => 26
        ];

        /*
         * The event is intentionally additive. Journal 3 or another theme
         * can consume these values through the account-link integration.
         */
        if (is_array($output)) {
            $output['ohbono_wallet'] = $link;
            $output['ohbono_wallet_history'] = $history;
        }
    }
}
