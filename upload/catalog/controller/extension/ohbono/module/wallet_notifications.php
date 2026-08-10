<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletNotifications extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/ohbono/module/wallet_notifications');
        if (!$this->customer->isLogged()) { $this->response->redirect($this->url->link('account/login')); return; }
        if (!$this->config->get('ohbono_wallet_status')) { $this->response->redirect($this->url->link('account/account')); return; }

        $this->load->model('extension/ohbono/module/wallet_notifications');
        $customer_id = (int)$this->customer->getId();
        $rows = $this->model_extension_ohbono_module_wallet_notifications->getNotifications($customer_id, 0, 20);

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_no_notifications'] = $this->language->get('text_no_notifications');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');
        $data['text_refund'] = $this->language->get('text_refund');
        $data['text_mark_read'] = $this->language->get('text_mark_read');
        $data['notifications'] = [];

        foreach ($rows as $row) {
            $label = $row['type'] === 'order_refund'
                ? $data['text_refund']
                : ($row['direction'] === 'credit' ? $data['text_credit'] : $data['text_debit']);
            $data['notifications'][] = [
                'notification_id' => (int)$row['notification_id'],
                'transaction_id' => (int)$row['transaction_id'],
                'title' => $label,
                'message' => (string)$row['message'],
                'date_added' => (string)$row['date_added'],
                'is_read' => (int)$row['is_read'],
                'transaction_href' => $this->url->link('extension/ohbono/module/wallet_transaction', 'transaction_id=' . (int)$row['transaction_id'])
            ];
        }

        $data['wallet'] = $this->url->link('extension/ohbono/module/wallet');
        $data['mark_read_url'] = $this->url->link('extension/ohbono/module/wallet_notifications.markRead');
        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $this->response->setOutput($this->load->view('extension/ohbono/module/wallet_notifications', $data));
    }

    public function markRead(): void {
        if (!$this->customer->isLogged()) { $this->json(['success' => false]); return; }
        $id = (int)($this->request->post['notification_id'] ?? 0);
        $this->load->model('extension/ohbono/module/wallet_notifications');
        $ok = $this->model_extension_ohbono_module_wallet_notifications->markRead((int)$this->customer->getId(), $id);
        $this->json(['success' => $ok]);
    }

    private function json(array $data): void {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
