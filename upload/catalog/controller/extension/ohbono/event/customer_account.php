<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Event;

class CustomerAccount extends \Opencart\System\Engine\Controller
{
    /**
     * Safe account navigation data endpoint for Journal/other themes.
     *
     * Themes can request this endpoint and render the link in their account
     * navigation without changing OpenCart core files.
     */
    public function links(): void
    {
        $this->response->addHeader('Content-Type: application/json');

        $links = [];

        if ($this->customer->isLogged() &&
            $this->config->get('ohbono_wallet_status')) {

            $links[] = [
                'key' => 'wallet',
                'title' => 'OHBONO Wallet',
                'href' => $this->url->link(
                    'extension/ohbono/module/wallet.history'
                )
            ];
        }

        $this->response->setOutput(json_encode([
            'success' => true,
            'links' => $links
        ]));
    }
}
