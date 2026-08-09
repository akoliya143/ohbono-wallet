<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletSettings extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_settings');

        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_settings'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $this->save();
            return;
        }

        $data = [];

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_maximum_use'] = $this->language->get('entry_maximum_use');
        $data['entry_reservation_ttl'] = $this->language->get('entry_reservation_ttl');
        $data['help_maximum_use'] = $this->language->get('help_maximum_use');
        $data['help_reservation_ttl'] = $this->language->get('help_reservation_ttl');
        $data['button_save'] = $this->language->get('button_save');

        $data['status'] = (int)$this->config->get('ohbono_wallet_status');
        $data['sort_order'] = (int)$this->config->get('ohbono_wallet_sort_order');
        $data['maximum_use'] = (float)$this->config->get('ohbono_wallet_maximum_use');
        $data['reservation_ttl'] = (int)$this->config->get('ohbono_wallet_reservation_ttl');

        if ($data['reservation_ttl'] < 300) {
            $data['reservation_ttl'] = 1800;
        }

        $data['action'] = $this->url->link(
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
        $status = !empty($this->request->post['status']) ? 1 : 0;
        $sort_order = (int)($this->request->post['sort_order'] ?? 0);
        $maximum_use = round(
            (float)($this->request->post['maximum_use'] ?? 0),
            4
        );
        $reservation_ttl = (int)(
            $this->request->post['reservation_ttl'] ?? 1800
        );

        if ($sort_order < 0) {
            $sort_order = 0;
        }

        if ($maximum_use < 0) {
            $maximum_use = 0;
        }

        if ($reservation_ttl < 300) {
            $reservation_ttl = 300;
        }

        $this->db->query(
            "DELETE FROM `" . DB_PREFIX . "setting`
             WHERE `store_id` = '0'
             AND `code` = 'ohbono_wallet'"
        );

        $settings = [
            'ohbono_wallet_status' => $status,
            'ohbono_wallet_sort_order' => $sort_order,
            'ohbono_wallet_maximum_use' => $maximum_use,
            'ohbono_wallet_reservation_ttl' => $reservation_ttl
        ];

        foreach ($settings as $key => $value) {
            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "setting`
                 SET `store_id` = '0',
                     `code` = 'ohbono_wallet',
                     `key` = '" . $this->db->escape($key) . "',
                     `value` = '" . $this->db->escape((string)$value) . "'"
            );
        }

        $this->session->data['success'] =
            $this->language->get('text_success');

        $this->response->redirect(
            $this->url->link(
                'extension/ohbono/module/wallet_settings',
                'user_token=' . $this->session->data['user_token']
            )
        );
    }
}
