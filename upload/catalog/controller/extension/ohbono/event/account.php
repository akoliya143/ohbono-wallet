<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Event;

class Account extends \Opencart\System\Engine\Controller
{
    /**
     * Event target: account page output.
     *
     * This method intentionally returns an empty string unless a store-level
     * event integration explicitly needs a rendered wallet link.
     */
    public function account(): string
    {
        if (!$this->customer->isLogged()) {
            return '';
        }

        return $this->load->controller(
            'extension/ohbono/account/account.walletLink'
        );
    }

    /**
     * Generic menu item endpoint for Journal/custom account navigation.
     */
    public function menu(): void
    {
        $data = $this->load->controller(
            'extension/ohbono/account/account.walletMenuItem'
        );

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => !empty($data),
            'item' => $data
        ]));
    }
}
