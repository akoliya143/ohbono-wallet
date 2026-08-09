<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

use RuntimeException;

class WalletCustomer extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_customer');
        $this->load->model('extension/ohbono/module/wallet_customer');

        $page = max(1, (int)($this->request->get['page'] ?? 1));
        $limit = 25;
        $start = ($page - 1) * $limit;

        $filter = trim((string)($this->request->get['filter_customer'] ?? ''));

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['entry_customer'] = $this->language->get('entry_customer');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_email'] = $this->language->get('column_email');
        $data['column_balance'] = $this->language->get('column_balance');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_action'] = $this->language->get('column_action');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_manage'] = $this->language->get('button_manage');

        $currency = $this->config->get('config_currency');

        $data['wallets'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_customer->getWallets([
            'filter_customer' => $filter,
            'start' => $start,
            'limit' => $limit
        ]) as $wallet) {
            $data['wallets'][] = [
                'customer_id' => (int)$wallet['customer_id'],
                'customer' => trim($wallet['firstname'] . ' ' . $wallet['lastname'])
                    ?: ('Customer #' . (int)$wallet['customer_id']),
                'email' => $wallet['email'],
                'balance' => $this->currency->format((float)$wallet['balance'], $currency),
                'status' => (int)$wallet['status'],
                'manage' => $this->url->link(
                    'extension/ohbono/module/wallet_customer.form',
                    'user_token=' . $this->session->data['user_token'] .
                    '&customer_id=' . (int)$wallet['customer_id']
                )
            ];
        }

        $total = $this->model_extension_ohbono_module_wallet_customer->getTotalWallets([
            'filter_customer' => $filter
        ]);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token'] .
            '&filter_customer=' . urlencode($filter) .
            '&page={page}'
        );

        $data['pagination'] = $pagination->render();
        $data['filter_customer'] = $filter;

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet_customer_list', $data)
        );
    }

    public function form(): void
    {
        $this->load->language('extension/ohbono/module/wallet_customer');
        $this->load->model('extension/ohbono/module/wallet_customer');

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);

        if ($customer_id <= 0) {
            $this->response->redirect($this->url->link(
                'extension/ohbono/module/wallet_customer',
                'user_token=' . $this->session->data['user_token']
            ));

            return;
        }

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet_customer')) {
                $data['error_warning'] = $this->language->get('error_permission');
            } else {
                try {
                    $direction = (string)($this->request->post['direction'] ?? '');
                    $amount = (float)($this->request->post['amount'] ?? 0);
                    $reference = trim((string)($this->request->post['reference'] ?? ''));
                    $comment = trim((string)($this->request->post['comment'] ?? ''));

                    if (!in_array($direction, ['credit', 'debit'], true)) {
                        throw new RuntimeException($this->language->get('error_direction'));
                    }

                    if ($amount <= 0) {
                        throw new RuntimeException($this->language->get('error_amount'));
                    }

                    $this->model_extension_ohbono_module_wallet_customer->adjust(
                        $customer_id,
                        $direction,
                        $amount,
                        $reference,
                        $comment
                    );

                    $this->session->data['success'] = $this->language->get('text_success');

                    $this->response->redirect($this->url->link(
                        'extension/ohbono/module/wallet_customer.form',
                        'user_token=' . $this->session->data['user_token'] .
                        '&customer_id=' . $customer_id
                    ));

                    return;
                } catch (\Throwable $e) {
                    $data['error_warning'] = $e->getMessage();
                }
            }
        }

        $wallet = $this->model_extension_ohbono_module_wallet_customer->getWallet($customer_id);

        if (!$wallet) {
            $data['error_warning'] = $this->language->get('error_wallet');
            $wallet = [
                'customer_id' => $customer_id,
                'firstname' => '',
                'lastname' => '',
                'email' => '',
                'balance' => 0,
                'status' => 0
            ];
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_manage'] = $this->language->get('text_manage');
        $data['text_history'] = $this->language->get('text_history');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_direction'] = $this->language->get('entry_direction');
        $data['entry_amount'] = $this->language->get('entry_amount');
        $data['entry_reference'] = $this->language->get('entry_reference');
        $data['entry_comment'] = $this->language->get('entry_comment');

        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');

        $data['column_date'] = $this->language->get('column_date');
        $data['column_type'] = $this->language->get('column_type');
        $data['column_direction'] = $this->language->get('column_direction');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_before'] = $this->language->get('column_before');
        $data['column_after'] = $this->language->get('column_after');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_comment'] = $this->language->get('column_comment');

        $data['customer_id'] = $customer_id;
        $data['customer'] = trim($wallet['firstname'] . ' ' . $wallet['lastname']);
        $data['email'] = $wallet['email'];
        $data['balance'] = $this->currency->format(
            (float)$wallet['balance'],
            $this->config->get('config_currency')
        );
        $data['status'] = (int)$wallet['status'];

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_customer->getTransactions($customer_id) as $transaction) {
            $data['transactions'][] = [
                'date' => $transaction['date_added'],
                'type' => ucwords(str_replace('_', ' ', $transaction['type'])),
                'direction' => $transaction['direction'],
                'amount' => $this->currency->format(
                    (float)$transaction['amount'],
                    $this->config->get('config_currency')
                ),
                'before' => $this->currency->format(
                    (float)$transaction['balance_before'],
                    $this->config->get('config_currency')
                ),
                'after' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $this->config->get('config_currency')
                ),
                'reference' => $transaction['reference'],
                'comment' => $transaction['comment']
            ];
        }

        $data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_customer.form',
            'user_token=' . $this->session->data['user_token'] .
            '&customer_id=' . $customer_id
        );

        $data['back'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet_customer_form', $data)
        );
    }
}
