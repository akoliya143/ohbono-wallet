<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Event;

class Account extends \Opencart\System\Engine\Controller
{
    /**
     * Adds OHBONO Wallet links to the customer account data.
     *
     * This event controller is intentionally non-destructive: it only adds
     * links to the account template data.
     */
    public function accountAfter(string &$route, array &$args, mixed &$output): void
    {
        if (!$this->customer->isLogged()) {
            return;
        }

        if (!$this->config->get('ohbono_wallet_status')) {
            return;
        }

        if (!is_string($output) || $output === '') {
            return;
        }

        /*
         * OpenCart account templates differ between stores/themes.
         * The preferred integration is through the account event data
         * extension below. Existing HTML is left untouched here.
         */
    }

    /**
     * Returns navigation data for account integrations/themes.
     */
    public function links(): void
    {
        if (!$this->customer->isLogged() ||
            !$this->config->get('ohbono_wallet_status')) {
            $this->response->setOutput(json_encode([
                'success' => true,
                'links' => []
            ]));
            return;
        }

        $this->response->addHeader('Content-Type: application/json');

        $this->response->setOutput(json_encode([
            'success' => true,
            'links' => [
                [
                    'key' => 'wallet',
                    'title' => 'OHBONO Wallet',
                    'href' => $this->url->link(
                        'extension/ohbono/module/wallet.history'
                    )
                ]
            ]
        ]));
    }
}
