<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        $this->load->language('extension/ohbono/payment/wallet');

        $data['name'] = 'wallet';
        $data['code'] = 'wallet';
        $data['title'] = $this->language->get('text_wallet');
        $data['description'] = $this->language->get('text_description');

        $this->load->model('extension/ohbono/total/wallet');

        $data['balance'] = 0.0;
        $data['wallet_used'] = 0.0;

        if ($this->customer->isLogged()) {
            $customer_id = (int)$this->customer->getId();

            $data['balance'] = $this->model_extension_ohbono_total_wallet
                ->getAvailableBalance($customer_id);

            $data['wallet_used'] = $this->model_extension_ohbono_total_wallet
                ->getSessionWalletUse();
        }

        $data['text_available_balance'] = $this->language->get('text_available_balance');
        $data['text_wallet_used'] = $this->language->get('text_wallet_used');
        $data['text_remaining'] = $this->language->get('text_remaining');
        $data['text_apply'] = $this->language->get('text_apply');
        $data['text_remove'] = $this->language->get('text_remove');
        $data['text_amount'] = $this->language->get('text_amount');

        return $this->load->view(
            'extension/ohbono/payment/wallet',
            $data
        );
    }
}
