<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Controller
{
    private const EXTENSION_CODE = 'ohbono_wallet';

    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet');
        $this->load->model('extension/ohbono/module/wallet');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $fields = [
                'status',
                'allow_checkout',
                'allow_partial_payment',
                'allow_full_payment',
                'refund_to_wallet',
                'minimum_use',
                'maximum_use',
                'sort_order'
            ];

            foreach ($fields as $field) {
                $value = $this->request->post[$field] ?? '';

                if (in_array($field, [
                    'status',
                    'allow_checkout',
                    'allow_partial_payment',
                    'allow_full_payment',
                    'refund_to_wallet'
                ], true)) {
                    $value = (string)(int)$value;
                }

                $this->model_extension_ohbono_module_wallet->saveSetting($field, (string)$value);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect(
                $this->url->link(
                    'extension/ohbono/module/wallet',
                    'user_token=' . $this->session->data['user_token']
                )
            );

            return;
        }

        $settings = $this->model_extension_ohbono_module_wallet->getSettings();

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_allow_checkout'] = $this->language->get('entry_allow_checkout');
        $data['entry_allow_partial_payment'] = $this->language->get('entry_allow_partial_payment');
        $data['entry_allow_full_payment'] = $this->language->get('entry_allow_full_payment');
        $data['entry_refund_to_wallet'] = $this->language->get('entry_refund_to_wallet');
        $data['entry_minimum_use'] = $this->language->get('entry_minimum_use');
        $data['entry_maximum_use'] = $this->language->get('entry_maximum_use');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet',
            'user_token=' . $this->session->data['user_token']
        );

        $data['cancel'] = $this->url->link(
            'extension/marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=module'
        );

        $data['status'] = (int)($settings['status'] ?? 1);
        $data['allow_checkout'] = (int)($settings['allow_checkout'] ?? 1);
        $data['allow_partial_payment'] = (int)($settings['allow_partial_payment'] ?? 1);
        $data['allow_full_payment'] = (int)($settings['allow_full_payment'] ?? 1);
        $data['refund_to_wallet'] = (int)($settings['refund_to_wallet'] ?? 1);
        $data['minimum_use'] = $settings['minimum_use'] ?? '0';
        $data['maximum_use'] = $settings['maximum_use'] ?? '0';
        $data['sort_order'] = $settings['sort_order'] ?? '1';

        $data['error_warning'] = $this->error['warning'] ?? '';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet', $data)
        );
    }

    protected function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet')) {
            $this->error['warning'] = $this->language->get('error_permission');

            return false;
        }

        return true;
    }

    public function install(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet')) {
            return;
        }

        $this->load->model('extension/ohbono/module/wallet');

        $defaults = [
            'status' => '1',
            'allow_checkout' => '1',
            'allow_partial_payment' => '1',
            'allow_full_payment' => '1',
            'refund_to_wallet' => '1',
            'minimum_use' => '0',
            'maximum_use' => '0',
            'sort_order' => '1'
        ];

        foreach ($defaults as $key => $value) {
            $this->model_extension_ohbono_module_wallet->saveSetting($key, $value);
        }

        $this->load->model('setting/event');

        $events = [
            [
                'code' => 'ohbono_wallet_customer_add',
                'trigger' => 'catalog/model/account/customer/addCustomer/after',
                'action' => 'extension/ohbono/module/wallet.customerAdd',
                'description' => 'Create an OHBONO wallet after customer creation.',
                'status' => 1,
                'sort_order' => 1
            ]
        ];

        foreach ($events as $event) {
            $this->model_setting_event->addEvent(
                $event['code'],
                $event['description'],
                $event['trigger'],
                $event['action'],
                $event['status'],
                $event['sort_order']
            );
        }
    }

    public function uninstall(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet')) {
            return;
        }

        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('ohbono_wallet_customer_add');
    }

    public function customerAdd(string &$route, array &$args, $output): void
    {
        $customer_id = 0;

        if (is_numeric($output)) {
            $customer_id = (int)$output;
        } elseif (isset($args[0]) && is_numeric($args[0])) {
            $customer_id = (int)$args[0];
        }

        if ($customer_id <= 0) {
            return;
        }

        $this->load->model('extension/ohbono/module/wallet');

        /*
         * This integration deliberately uses the wallet table directly only
         * for creation. All subsequent balance changes go through the
         * WalletService.
         */
        $query = $this->db->query(
            "SELECT `wallet_id`
             FROM `" . DB_PREFIX . "wallet`
             WHERE `customer_id` = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        if (!$query->num_rows) {
            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "wallet`
                 SET `customer_id` = '" . (int)$customer_id . "',
                     `balance` = '0.0000',
                     `status` = '1',
                     `date_added` = NOW(),
                     `date_modified` = NOW()"
            );
        }
    }
}
