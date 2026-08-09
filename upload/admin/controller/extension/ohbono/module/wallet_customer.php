<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletCustomer extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_customer');

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_customer'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);

        $data = [];

        $data['heading_title'] =
            $this->language->get('heading_title');
        $data['entry_customer'] =
            $this->language->get('entry_customer');
        $data['button_search'] =
            $this->language->get('button_search');
        $data['button_adjust'] =
            $this->language->get('button_adjust');
        $data['text_balance'] =
            $this->language->get('text_balance');
        $data['text_status'] =
            $this->language->get('text_status');
        $data['text_active'] =
            $this->language->get('text_active');
        $data['text_disabled'] =
            $this->language->get('text_disabled');
        $data['text_transactions'] =
            $this->language->get('text_transactions');
        $data['text_no_transactions'] =
            $this->language->get('text_no_transactions');

        $data['customer_id'] = $customer_id;
        $data['customer'] = null;
        $data['transactions'] = [];

        $this->load->model('extension/ohbono/module/wallet_customer');

        if ($customer_id > 0) {
            $data['customer'] =
                $this->model_extension_ohbono_module_wallet_customer
                    ->getWallet($customer_id);

            $data['transactions'] =
                $this->model_extension_ohbono_module_wallet_customer
                    ->getTransactions($customer_id, 0, 50);
        }

        $data['adjust_url'] = $this->url->link(
            'extension/ohbono/module/wallet_adjustment',
            'user_token=' . $this->session->data['user_token']
        );

        $data['search_url'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_customer',
                $data
            )
        );
    }
}
