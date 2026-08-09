<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletPayment extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_payment'
        );

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_payment'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['text_status'] =
            $this->language->get('text_status');

        $data['text_enabled'] =
            $this->language->get('text_enabled');

        $data['text_disabled'] =
            $this->language->get('text_disabled');

        $data['status'] = (int)$this->config->get(
            'ohbono_wallet_status'
        );

        $data['sort_order'] = (int)$this->config->get(
            'ohbono_wallet_sort_order'
        );

        $data['maximum_use'] = (float)$this->config->get(
            'ohbono_wallet_maximum_use'
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_payment',
                $data
            )
        );
    }
}
