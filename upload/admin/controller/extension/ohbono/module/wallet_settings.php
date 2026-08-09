<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletSettings extends \Opencart\System\Engine\Controller
{
    private const SETTINGS = [
        'ohbono_wallet_status',
        'ohbono_wallet_allow_checkout',
        'ohbono_wallet_minimum_use',
        'ohbono_wallet_maximum_use',
        'ohbono_wallet_history_limit',
        'ohbono_wallet_sort_order'
    ];

    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_settings');

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_settings'
        )) {
            $this->response->setOutput($this->language->get('error_permission'));
            return;
        }

        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            if (!$this->user->hasPermission(
                'modify',
                'extension/ohbono/module/wallet_settings'
            )) {
                $this->session->data['error'] = $this->language->get('error_permission');
            } else {
                $this->save();
            }

            $this->response->redirect($this->url->link(
                'extension/ohbono/module/wallet_settings',
                'user_token=' . $this->session->data['user_token']
            ));
            return;
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_help'] = $this->language->get('text_help');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_allow_checkout'] = $this->language->get('entry_allow_checkout');
        $data['entry_minimum_use'] = $this->language->get('entry_minimum_use');
        $data['entry_maximum_use'] = $this->language->get('entry_maximum_use');
        $data['entry_history_limit'] = $this->language->get('entry_history_limit');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['help_minimum_use'] = $this->language->get('help_minimum_use');
        $data['help_maximum_use'] = $this->language->get('help_maximum_use');
        $data['help_history_limit'] = $this->language->get('help_history_limit');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['error_warning'] = $this->session->data['error'] ?? '';
        unset($this->session->data['error']);

        foreach (self::SETTINGS as $setting) {
            $data[$setting] = $this->config->get($setting);
        }

        $data['save'] = $this->url->link(
            'extension/ohbono/module/wallet_settings',
            'user_token=' . $this->session->data['user_token']
        );

        $data['cancel'] = $this->url->link(
            'extension/ohbono/module/wallet_settings',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_settings',
                $data
            )
        );
    }

    private function save(): void
    {
        $this->load->model('setting/setting');

        $status = !empty($this->request->post['ohbono_wallet_status']) ? 1 : 0;
        $allow_checkout = !empty($this->request->post['ohbono_wallet_allow_checkout']) ? 1 : 0;

        $minimum = round(
            max(0, (float)($this->request->post['ohbono_wallet_minimum_use'] ?? 0)),
            4
        );

        $maximum = round(
            max(0, (float)($this->request->post['ohbono_wallet_maximum_use'] ?? 0)),
            4
        );

        $history_limit = max(
            5,
            min(100, (int)($this->request->post['ohbono_wallet_history_limit'] ?? 20))
        );

        $sort_order = (int)($this->request->post['ohbono_wallet_sort_order'] ?? 0);

        if ($maximum > 0 && $minimum > $maximum) {
            $this->session->data['error'] =
                $this->language->get('error_minimum_greater_maximum');

            return;
        }

        $this->model_setting_setting->editSetting('ohbono_wallet', [
            'ohbono_wallet_status' => $status,
            'ohbono_wallet_allow_checkout' => $allow_checkout,
            'ohbono_wallet_minimum_use' => $minimum,
            'ohbono_wallet_maximum_use' => $maximum,
            'ohbono_wallet_history_limit' => $history_limit,
            'ohbono_wallet_sort_order' => $sort_order
        ]);

        $this->session->data['success'] =
            $this->language->get('text_success');
    }
}
