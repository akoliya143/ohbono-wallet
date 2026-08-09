<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletTransactions extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_transactions');

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_transactions'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->model('extension/ohbono/module/wallet_transactions');

        $page = max(
            1,
            (int)($this->request->get['page'] ?? 1)
        );

        $filter_customer_id = (int)(
            $this->request->get['filter_customer_id'] ?? 0
        );

        $filter_order_id = (int)(
            $this->request->get['filter_order_id'] ?? 0
        );

        $filter_type = trim(
            (string)($this->request->get['filter_type'] ?? '')
        );

        $filter_direction = trim(
            (string)($this->request->get['filter_direction'] ?? '')
        );

        $limit = 50;
        $start = ($page - 1) * $limit;

        $filters = [
            'customer_id' => $filter_customer_id,
            'order_id' => $filter_order_id,
            'type' => $filter_type,
            'direction' => $filter_direction,
            'start' => $start,
            'limit' => $limit
        ];

        $data['transactions'] =
            $this->model_extension_ohbono_module_wallet_transactions
                ->getTransactions($filters);

        $total =
            $this->model_extension_ohbono_module_wallet_transactions
                ->getTotalTransactions($filters);

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['text_no_results'] =
            $this->language->get('text_no_results');

        $data['filter_customer_id'] = $filter_customer_id;
        $data['filter_order_id'] = $filter_order_id;
        $data['filter_type'] = $filter_type;
        $data['filter_direction'] = $filter_direction;

        $data['pagination'] = '';

        if ($total > $limit) {
            $pagination = new \Opencart\System\Library\Pagination();
            $pagination->total = $total;
            $pagination->page = $page;
            $pagination->limit = $limit;

            $pagination->url = $this->url->link(
                'extension/ohbono/module/wallet_transactions',
                'user_token=' .
                $this->session->data['user_token'] .
                '&page={page}' .
                '&filter_customer_id=' . $filter_customer_id .
                '&filter_order_id=' . $filter_order_id .
                '&filter_type=' . urlencode($filter_type) .
                '&filter_direction=' . urlencode($filter_direction)
            );

            $data['pagination'] = $pagination->render();
        }

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_transactions',
                $data
            )
        );
    }
}
