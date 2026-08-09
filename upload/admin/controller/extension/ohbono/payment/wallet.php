<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');

        $this->load->model('setting/setting');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_wallet', [
                'payment_wallet_status' => (int)($this->request->post['payment_wallet_status'] ?? 0),
                'payment_wallet_sort_order' => (int)($this->request->post['payment_wallet_sort_order'] ?? 1)
            ]);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/ohbono/payment/wallet',
                'user_token=' . $this->session->data['user_token']
            ));

            return;
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['success'] = $this->session->data['success'] ?? '';

        unset($this->session->data['success']);

        $data['action'] = $this->url->link(
            'extension/ohbono/payment/wallet',
            'user_token=' . $this->session->data['user_token']
        );

        $data['cancel'] = $this->url->link(
            'extension/marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=payment'
        );

        $data['payment_wallet_status'] = (int)$this->config->get('payment_wallet_status');
        $data['payment_wallet_sort_order'] = (int)$this->config->get('payment_wallet_sort_order');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/payment/wallet', $data)
        );
    }

    public function install(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/payment/wallet')) {
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting('payment_wallet', [
            'payment_wallet_status' => 1,
            'payment_wallet_sort_order' => 1
        ]);
    }

    public function uninstall(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/payment/wallet')) {
            return;
        }

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('payment_wallet');
    }

    protected function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/payment/wallet')) {
            $this->error['warning'] = $this->language->get('error_permission');

            return false;
        }

        return true;
    }
}
