<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getMethod(array $address = []): array
    {
        if (!$this->customer->isLogged()) {
            return [];
        }

        if (!(int)$this->config->get('ohbono_wallet_status') ||
            !(int)$this->config->get('ohbono_wallet_allow_checkout')) {
            return [];
        }

        $this->load->model('extension/ohbono/total/wallet');

        $balance = $this->model_extension_ohbono_total_wallet
            ->getAvailableBalance((int)$this->customer->getId());

        if ($balance <= 0) {
            return [];
        }

        return [
            'code' => 'wallet',
            'name' => $this->language->get('text_wallet'),
            'option' => [
                'wallet' => [
                    'code' => 'wallet.wallet',
                    'name' => $this->language->get('text_wallet')
                ]
            ],
            'sort_order' => (int)$this->config->get('ohbono_wallet_sort_order'),
            'error' => ''
        ];
    }
}
