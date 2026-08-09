<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletTransaction extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_transaction');
        $this->load->model('extension/ohbono/module/wallet_transaction');

        $page = max(1, (int)($this->request->get['page'] ?? 1));
        $limit = 25;
        $start = ($page - 1) * $limit;

        $filters = [
            'filter_customer' => trim((string)($this->request->get['filter_customer'] ?? '')),
            'filter_type' => trim((string)($this->request->get['filter_type'] ?? '')),
            'filter_direction' => trim((string)($this->request->get['filter_direction'] ?? '')),
            'filter_order_id' => (int)($this->request->get['filter_order_id'] ?? 0),
            'filter_date_start' => trim((string)($this->request->get['filter_date_start'] ?? '')),
            'filter_date_end' => trim((string)($this->request->get['filter_date_end'] ?? ''))
        ];

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_all'] = $this->language->get('text_all');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');

        $data['entry_customer'] = $this->language->get('entry_customer');
        $data['entry_type'] = $this->language->get('entry_type');
        $data['entry_direction'] = $this->language->get('entry_direction');
        $data['entry_order_id'] = $this->language->get('entry_order_id');
        $data['entry_date_start'] = $this->language->get('entry_date_start');
        $data['entry_date_end'] = $this->language->get('entry_date_end');

        $data['column_id'] = $this->language->get('column_id');
        $data['column_date'] = $this->language->get('column_date');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_type'] = $this->language->get('column_type');
        $data['column_direction'] = $this->language->get('column_direction');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_before'] = $this->language->get('column_before');
        $data['column_after'] = $this->language->get('column_after');
        $data['column_order'] = $this->language->get('column_order');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_comment'] = $this->language->get('column_comment');
        $data['column_action'] = $this->language->get('column_action');

        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_view'] = $this->language->get('button_view');

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_transaction->getTransactions([
            'filter_customer' => $filters['filter_customer'],
            'filter_type' => $filters['filter_type'],
            'filter_direction' => $filters['filter_direction'],
            'filter_order_id' => $filters['filter_order_id'],
            'filter_date_start' => $filters['filter_date_start'],
            'filter_date_end' => $filters['filter_date_end'],
            'start' => $start,
            'limit' => $limit
        ]) as $transaction) {
            $data['transactions'][] = [
                'transaction_id' => (int)$transaction['transaction_id'],
                'date' => $transaction['date_added'],
                'customer_id' => (int)$transaction['customer_id'],
                'customer' => trim($transaction['firstname'] . ' ' . $transaction['lastname'])
                    ?: ('Customer #' . (int)$transaction['customer_id']),
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
                'order_id' => (int)$transaction['order_id'],
                'reference' => $transaction['reference'],
                'comment' => $transaction['comment'],
                'view' => $this->url->link(
                    'extension/ohbono/module/wallet_transaction.info',
                    'user_token=' . $this->session->data['user_token'] .
                    '&transaction_id=' . (int)$transaction['transaction_id']
                )
            ];
        }

        $total = $this->model_extension_ohbono_module_wallet_transaction->getTotalTransactions($filters);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/ohbono/module/wallet_transaction',
            'user_token=' . $this->session->data['user_token'] .
            $this->buildQuery($filters) .
            '&page={page}'
        );

        $data['pagination'] = $pagination->render();
        $data['filters'] = $filters;

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_transaction',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet_transaction_list', $data)
        );
    }

    public function info(): void
    {
        $this->load->language('extension/ohbono/module/wallet_transaction');
        $this->load->model('extension/ohbono/module/wallet_transaction');

        $transaction_id = (int)($this->request->get['transaction_id'] ?? 0);

        $transaction = $this->model_extension_ohbono_module_wallet_transaction->getTransaction($transaction_id);

        if (!$transaction) {
            $this->response->redirect($this->url->link(
                'extension/ohbono/module/wallet_transaction',
                'user_token=' . $this->session->data['user_token']
            ));

            return;
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_details'] = $this->language->get('text_details');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');

        $data['label_transaction_id'] = $this->language->get('label_transaction_id');
        $data['label_date'] = $this->language->get('label_date');
        $data['label_customer'] = $this->language->get('label_customer');
        $data['label_type'] = $this->language->get('label_type');
        $data['label_direction'] = $this->language->get('label_direction');
        $data['label_amount'] = $this->language->get('label_amount');
        $data['label_before'] = $this->language->get('label_before');
        $data['label_after'] = $this->language->get('label_after');
        $data['label_order'] = $this->language->get('label_order');
        $data['label_reference'] = $this->language->get('label_reference');
        $data['label_comment'] = $this->language->get('label_comment');
        $data['label_admin'] = $this->language->get('label_admin');

        $data['transaction'] = [
            'transaction_id' => (int)$transaction['transaction_id'],
            'date' => $transaction['date_added'],
            'customer' => trim($transaction['firstname'] . ' ' . $transaction['lastname'])
                ?: ('Customer #' . (int)$transaction['customer_id']),
            'customer_id' => (int)$transaction['customer_id'],
            'type' => ucwords(str_replace('_', ' ', $transaction['type'])),
            'direction' => $transaction['direction'],
            'amount' => $this->currency->format((float)$transaction['amount'], $this->config->get('config_currency')),
            'before' => $this->currency->format((float)$transaction['balance_before'], $this->config->get('config_currency')),
            'after' => $this->currency->format((float)$transaction['balance_after'], $this->config->get('config_currency')),
            'order_id' => (int)$transaction['order_id'],
            'reference' => $transaction['reference'],
            'comment' => $transaction['comment'],
            'admin_user_id' => (int)$transaction['admin_user_id']
        ];

        $data['back'] = $this->url->link(
            'extension/ohbono/module/wallet_transaction',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet_transaction_info', $data)
        );
    }

    private function buildQuery(array $filters): string
    {
        $query = '';

        foreach ($filters as $key => $value) {
            if ($value !== '' && $value !== 0) {
                $query .= '&' . $key . '=' . urlencode((string)$value);
            }
        }

        return $query;
    }
}
