<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletAdjustment extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_adjustment');

        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_adjustment'
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
        $data['entry_amount'] =
            $this->language->get('entry_amount');
        $data['entry_reason'] =
            $this->language->get('entry_reason');
        $data['help_amount'] =
            $this->language->get('help_amount');
        $data['button_submit'] =
            $this->language->get('button_submit');
        $data['button_back'] =
            $this->language->get('button_back');

        $data['customer_id'] = $customer_id;

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_adjustment.adjust',
            'user_token=' . $this->session->data['user_token']
        );

        $data['back'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token'] .
            '&customer_id=' . $customer_id
        );

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_adjustment',
                $data
            )
        );
    }

    public function adjust(): void
    {
        $this->response->addHeader('Content-Type: application/json');

        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_adjustment'
        )) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Permission denied.'
            ]));
            return;
        }

        $customer_id = (int)($this->request->post['customer_id'] ?? 0);
        $amount = round(
            (float)($this->request->post['amount'] ?? 0),
            4
        );
        $reason = trim(
            (string)($this->request->post['reason'] ?? '')
        );

        if ($customer_id <= 0) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Customer ID is required.'
            ]));
            return;
        }

        if ($amount == 0) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Adjustment amount cannot be zero.'
            ]));
            return;
        }

        if ($reason === '') {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'A reason is required.'
            ]));
            return;
        }

        $this->load->library('ohbono/wallet_service');

        try {
            $reference = 'ADMIN-' .
                (int)$this->user->getId() . '-' .
                bin2hex(random_bytes(8));

            if ($amount > 0) {
                $transaction_id =
                    $this->wallet_service->credit(
                        $customer_id,
                        $amount,
                        'admin_adjustment',
                        $reference,
                        $reason,
                        0,
                        (int)$this->user->getId()
                    );
            } else {
                $transaction_id =
                    $this->wallet_service->debit(
                        $customer_id,
                        abs($amount),
                        'admin_adjustment',
                        $reference,
                        $reason,
                        0,
                        (int)$this->user->getId()
                    );
            }

            $this->response->setOutput(json_encode([
                'success' => true,
                'transaction_id' => (int)$transaction_id
            ]));
        } catch (\Throwable $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
}
