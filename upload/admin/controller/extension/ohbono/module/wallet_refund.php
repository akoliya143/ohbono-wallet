<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletRefund extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_refund');

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_refund'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->model('extension/ohbono/module/wallet_refund');

        $page = max(1, (int)($this->request->get['page'] ?? 1));
        $limit = 50;
        $start = ($page - 1) * $limit;

        $filters = [
            'order_id' => (int)($this->request->get['filter_order_id'] ?? 0),
            'customer_id' => (int)($this->request->get['filter_customer_id'] ?? 0),
            'date_start' => trim((string)($this->request->get['filter_date_start'] ?? '')),
            'date_end' => trim((string)($this->request->get['filter_date_end'] ?? ''))
        ];

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');

        $data['column_date'] = $this->language->get('column_date');
        $data['column_order'] = $this->language->get('column_order');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_transaction'] = $this->language->get('column_transaction');

        $data['entry_order_id'] = $this->language->get('entry_order_id');
        $data['entry_customer_id'] = $this->language->get('entry_customer_id');
        $data['entry_date_start'] = $this->language->get('entry_date_start');
        $data['entry_date_end'] = $this->language->get('entry_date_end');

        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        $data['refunds'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_refund
            ->getRefunds($filters, $start, $limit) as $refund) {

            $data['refunds'][] = [
                'wallet_order_id' => (int)$refund['wallet_order_id'],
                'order_id' => (int)$refund['order_id'],
                'customer_id' => (int)$refund['customer_id'],
                'customer' => trim(
                    $refund['firstname'] . ' ' . $refund['lastname']
                ),
                'email' => $refund['email'],
                'amount' => $this->currency->format(
                    (float)$refund['amount'],
                    $this->config->get('config_currency')
                ),
                'transaction_id' => (int)$refund['transaction_id'],
                'reference' => $refund['reference'],
                'date' => $refund['date_added']
            ];
        }

        $total = $this->model_extension_ohbono_module_wallet_refund
            ->getTotalRefunds($filters);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/ohbono/module/wallet_refund',
            'user_token=' . $this->session->data['user_token'] .
            $this->buildQuery($filters) .
            '&page={page}'
        );

        $data['pagination'] = $pagination->render();
        $data['filters'] = $filters;

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_refund',
            'user_token=' . $this->session->data['user_token']
        );

        $data['reset'] = $data['action'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_refund',
                $data
            )
        );
    }

    private function buildQuery(array $filters): string
    {
        $query = '';

        if ($filters['order_id']) {
            $query .= '&filter_order_id=' . (int)$filters['order_id'];
        }

        if ($filters['customer_id']) {
            $query .= '&filter_customer_id=' . (int)$filters['customer_id'];
        }

        if ($filters['date_start'] !== '') {
            $query .= '&filter_date_start=' . urlencode($filters['date_start']);
        }

        if ($filters['date_end'] !== '') {
            $query .= '&filter_date_end=' . urlencode($filters['date_end']);
        }

        return $query;
    }
}
