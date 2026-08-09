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

        $filters['start'] = $start;
        $filters['limit'] = $limit;

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_transaction->getTransactions($filters) as $transaction) {
            $customer = trim($transaction['firstname'] . ' ' . $transaction['lastname']);

            $data['transactions'][] = [
                'transaction_id' => (int)$transaction['transaction_id'],
                'date' => $transaction['date_added'],
                'customer' => $customer !== '' ? $customer : ('Customer #' . (int)$transaction['customer_id']),
                'email' => $transaction['email'],
                'order_id' => (int)$transaction['order_id'],
                'type' => ucwords(str_replace('_', ' ', $transaction['type'])),
                'direction' => $transaction['direction'],
                'amount' => ($transaction['direction'] === 'credit' ? '+' : '-') .
                    $this->currency->format((float)$transaction['amount'], $this->config->get('config_currency')),
                'balance_before' => $this->currency->format((float)$transaction['balance_before'], $this->config->get('config_currency')),
                'balance_after' => $this->currency->format((float)$transaction['balance_after'], $this->config->get('config_currency')),
                'reference' => $transaction['reference'],
                'comment' => $transaction['comment']
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
            '&page={page}' .
            $this->buildFilterQuery($filters)
        );

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            $total ? $start + 1 : 0,
            min($start + $limit, $total),
            $total,
            $total ? ceil($total / $limit) : 1
        );

        $data['filters'] = $filters;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_all'] = $this->language->get('text_all');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');
        $data['column_date'] = $this->language->get('column_date');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_order'] = $this->language->get('column_order');
        $data['column_type'] = $this->language->get('column_type');
        $data['column_direction'] = $this->language->get('column_direction');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_balance_before'] = $this->language->get('column_balance_before');
        $data['column_balance_after'] = $this->language->get('column_balance_after');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_comment'] = $this->language->get('column_comment');
        $data['entry_customer'] = $this->language->get('entry_customer');
        $data['entry_type'] = $this->language->get('entry_type');
        $data['entry_direction'] = $this->language->get('entry_direction');
        $data['entry_order_id'] = $this->language->get('entry_order_id');
        $data['entry_date_start'] = $this->language->get('entry_date_start');
        $data['entry_date_end'] = $this->language->get('entry_date_end');
        $data['button_filter'] = $this->language->get('button_filter');

        $data['filter_url'] = $this->url->link(
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

    private function buildFilterQuery(array $filters): string
    {
        $query = '';

        $fields = [
            'filter_customer',
            'filter_type',
            'filter_direction',
            'filter_order_id',
            'filter_date_start',
            'filter_date_end'
        ];

        foreach ($fields as $field) {
            if (!isset($filters[$field]) || $filters[$field] === '' || $filters[$field] === 0) {
                continue;
            }

            $query .= '&' . $field . '=' . urlencode((string)$filters[$field]);
        }

        return $query;
    }
}
