<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Account;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link(
                'account/login',
                'language=' . $this->config->get('config_language')
            ));

            return;
        }

        $this->load->language('extension/ohbono/account/wallet');
        $this->load->model('extension/ohbono/account/wallet');

        $page = max(1, (int)($this->request->get['page'] ?? 1));
        $limit = 20;
        $start = ($page - 1) * $limit;

        $customer_id = (int)$this->customer->getId();

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_balance'] = $this->language->get('text_balance');
        $data['text_history'] = $this->language->get('text_history');
        $data['text_no_transactions'] = $this->language->get('text_no_transactions');

        $data['column_date'] = $this->language->get('column_date');
        $data['column_description'] = $this->language->get('column_description');
        $data['column_order'] = $this->language->get('column_order');
        $data['column_credit'] = $this->language->get('column_credit');
        $data['column_debit'] = $this->language->get('column_debit');
        $data['column_balance'] = $this->language->get('column_balance');

        $currency = $this->session->data['currency'] ?? $this->config->get('config_currency');

        $balance = $this->model_extension_ohbono_account_wallet->getBalance($customer_id);

        $data['balance'] = $this->currency->format($balance, $currency);

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_account_wallet->getTransactions(
            $customer_id,
            $start,
            $limit
        ) as $transaction) {
            $credit = $transaction['direction'] === 'credit'
                ? (float)$transaction['amount']
                : 0.0;

            $debit = $transaction['direction'] === 'debit'
                ? (float)$transaction['amount']
                : 0.0;

            $data['transactions'][] = [
                'date' => $transaction['date_added'],
                'description' => $transaction['comment'] ?: ucwords(
                    str_replace('_', ' ', $transaction['type'])
                ),
                'order_id' => (int)$transaction['order_id'],
                'credit' => $credit > 0 ? '+' . $this->currency->format($credit, $currency) : '-',
                'debit' => $debit > 0 ? '-' . $this->currency->format($debit, $currency) : '-',
                'balance' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $currency
                )
            ];
        }

        $total = $this->model_extension_ohbono_account_wallet->getTotalTransactions($customer_id);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/ohbono/account/wallet',
            'language=' . $this->config->get('config_language') . '&page={page}'
        );

        $data['pagination'] = $pagination->render();

        $data['back'] = $this->url->link(
            'account/account',
            'language=' . $this->config->get('config_language')
        );

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = '';

        $this->response->setOutput(
            $this->load->view('extension/ohbono/account/wallet', $data)
        );
    }
}
