<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletCustomer extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_customer');
        $this->load->model('extension/ohbono/module/wallet_customer');

        $page = max(1, (int)($this->request->get['page'] ?? 1));
        $limit = 25;
        $start = ($page - 1) * $limit;

        $filter = trim((string)($this->request->get['filter'] ?? ''));

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_email'] = $this->language->get('column_email');
        $data['column_balance'] = $this->language->get('column_balance');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_date_modified'] = $this->language->get('column_date_modified');
        $data['column_action'] = $this->language->get('column_action');

        $data['entry_customer'] = $this->language->get('entry_customer');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_view'] = $this->language->get('button_view');

        $data['customers'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_customer
            ->getCustomers([
                'filter' => $filter,
                'start' => $start,
                'limit' => $limit
            ]) as $customer) {

            $data['customers'][] = [
                'customer_id' => (int)$customer['customer_id'],
                'name' => trim($customer['firstname'] . ' ' . $customer['lastname']),
                'email' => $customer['email'],
                'balance' => $this->currency->format(
                    (float)$customer['balance'],
                    $this->config->get('config_currency')
                ),
                'status' => (int)$customer['wallet_status'],
                'date_modified' => $customer['date_modified'],
                'view' => $this->url->link(
                    'extension/ohbono/module/wallet_customer.info',
                    'user_token=' . $this->session->data['user_token'] .
                    '&customer_id=' . (int)$customer['customer_id']
                )
            ];
        }

        $total = $this->model_extension_ohbono_module_wallet_customer
            ->getTotalCustomers($filter);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token'] .
            '&filter=' . urlencode($filter) .
            '&page={page}'
        );

        $data['pagination'] = $pagination->render();
        $data['filter'] = $filter;

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_customer_list',
                $data
            )
        );
    }

    public function info(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_customer'
        )) {
            $this->response->setOutput('Permission denied.');
            return;
        }

        $this->load->language('extension/ohbono/module/wallet_customer');
        $this->load->model('extension/ohbono/module/wallet_customer');

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);

        $customer = $this->model_extension_ohbono_module_wallet_customer
            ->getCustomer($customer_id);

        if (!$customer) {
            $this->response->redirect($this->url->link(
                'extension/ohbono/module/wallet_customer',
                'user_token=' . $this->session->data['user_token']
            ));
            return;
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_details'] = $this->language->get('text_details');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['label_customer'] = $this->language->get('label_customer');
        $data['label_email'] = $this->language->get('label_email');
        $data['label_customer_id'] = $this->language->get('label_customer_id');
        $data['label_balance'] = $this->language->get('label_balance');
        $data['label_status'] = $this->language->get('label_status');
        $data['label_total_credited'] = $this->language->get('label_total_credited');
        $data['label_total_debited'] = $this->language->get('label_total_debited');
        $data['label_transaction_count'] = $this->language->get('label_transaction_count');

        $data['entry_amount'] = $this->language->get('entry_amount');
        $data['entry_reference'] = $this->language->get('entry_reference');
        $data['entry_comment'] = $this->language->get('entry_comment');

        $data['button_credit'] = $this->language->get('button_credit');
        $data['button_debit'] = $this->language->get('button_debit');
        $data['button_back'] = $this->language->get('button_back');

        $data['customer'] = [
            'customer_id' => $customer_id,
            'name' => trim($customer['firstname'] . ' ' . $customer['lastname']),
            'email' => $customer['email'],
            'balance' => $this->currency->format(
                (float)$customer['balance'],
                $this->config->get('config_currency')
            ),
            'status' => (int)$customer['status']
        ];

        $summary = $this->model_extension_ohbono_module_wallet_customer
            ->getSummary($customer_id);

        $data['summary'] = [
            'credited' => $this->currency->format(
                $summary['credited'],
                $this->config->get('config_currency')
            ),
            'debited' => $this->currency->format(
                $summary['debited'],
                $this->config->get('config_currency')
            ),
            'count' => $summary['count']
        ];

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_customer
            ->getTransactions($customer_id, 0, 20) as $transaction) {

            $data['transactions'][] = [
                'transaction_id' => (int)$transaction['transaction_id'],
                'date' => $transaction['date_added'],
                'type' => ucwords(str_replace('_', ' ', $transaction['type'])),
                'direction' => $transaction['direction'],
                'amount' => $this->currency->format(
                    (float)$transaction['amount'],
                    $this->config->get('config_currency')
                ),
                'balance' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $this->config->get('config_currency')
                ),
                'reference' => $transaction['reference'],
                'order_id' => (int)$transaction['order_id']
            ];
        }

        $data['credit_action'] = $this->url->link(
            'extension/ohbono/module/wallet_customer.credit',
            'user_token=' . $this->session->data['user_token'] .
            '&customer_id=' . $customer_id
        );

        $data['debit_action'] = $this->url->link(
            'extension/ohbono/module/wallet_customer.debit',
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
            $this->load->view(
                'extension/ohbono/module/wallet_customer_info',
                $data
            )
        );
    }

    public function credit(): void
    {
        $this->processAdjustment('credit');
    }

    public function debit(): void
    {
        $this->processAdjustment('debit');
    }

    private function processAdjustment(string $direction): void
    {
        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_customer'
        )) {
            $this->json([
                'success' => false,
                'error' => 'Permission denied.'
            ]);
            return;
        }

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);
        $amount = round((float)($this->request->post['amount'] ?? 0), 4);
        $reference = trim((string)($this->request->post['reference'] ?? ''));
        $comment = trim((string)($this->request->post['comment'] ?? ''));

        if ($customer_id <= 0 || $amount <= 0) {
            $this->json([
                'success' => false,
                'error' => 'Customer and a positive amount are required.'
            ]);
            return;
        }

        if ($amount > 100000000) {
            $this->json([
                'success' => false,
                'error' => 'The amount is above the permitted limit.'
            ]);
            return;
        }

        $this->load->model('extension/ohbono/module/wallet_customer');

        try {
            $result = $this->model_extension_ohbono_module_wallet_customer
                ->adjust(
                    $customer_id,
                    $direction,
                    $amount,
                    $reference,
                    $comment,
                    (int)$this->user->getId()
                );

            $this->json([
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'balance' => $this->currency->format(
                    $result['balance'],
                    $this->config->get('config_currency')
                )
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function json(array $data): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
