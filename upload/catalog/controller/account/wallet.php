<?php
namespace Opencart\Catalog\Controller\Account;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/wallet', '', true);
            $this->response->redirect($this->url->link('account/login', '', true));

            return;
        }

        $this->load->language('account/wallet');
        $this->load->model('account/wallet');

        $customer_id = (int)$this->customer->getId();

        $this->model_account_wallet->ensureWallet($customer_id);

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_balance'] = $this->language->get('text_balance');
        $data['text_transactions'] = $this->language->get('text_transactions');
        $data['text_no_transactions'] = $this->language->get('text_no_transactions');
        $data['column_date'] = $this->language->get('column_date');
        $data['column_type'] = $this->language->get('column_type');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_comment'] = $this->language->get('column_comment');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_balance'] = $this->language->get('column_balance');

        $data['balance'] = $this->currency->format(
            $this->model_account_wallet->getBalance($customer_id),
            $this->session->data['currency'] ?? $this->config->get('config_currency')
        );

        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $page = max(1, $page);

        $limit = 20;
        $start = ($page - 1) * $limit;

        $data['transactions'] = [];

        foreach ($this->model_account_wallet->getTransactions($customer_id, $start, $limit) as $transaction) {
            $amount = (float)$transaction['amount'];

            $data['transactions'][] = [
                'date' => date($this->language->get('date_format'), strtotime($transaction['date_added'])),
                'type' => $this->language->get('transaction_type_' . $transaction['type']) !== 'transaction_type_' . $transaction['type']
                    ? $this->language->get('transaction_type_' . $transaction['type'])
                    : ucwords(str_replace('_', ' ', $transaction['type'])),
                'reference' => $transaction['reference'],
                'comment' => $transaction['comment'],
                'amount' => ($transaction['direction'] === 'credit' ? '+' : '-') . $this->currency->format(
                    $amount,
                    $this->session->data['currency'] ?? $this->config->get('config_currency')
                ),
                'balance' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $this->session->data['currency'] ?? $this->config->get('config_currency')
                ),
                'direction' => $transaction['direction']
            ];
        }

        $total = $this->model_account_wallet->getTransactionCount($customer_id);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('account/wallet', 'page={page}');

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            $total ? $start + 1 : 0,
            min($start + $limit, $total),
            $total,
            ceil($total / $limit)
        );

        $data['back'] = $this->url->link('account/account');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('account/wallet', $data)
        );
    }
}
