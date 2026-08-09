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

        $customer_id = (int)$this->customer->getId();
        $page = max(1, (int)($this->request->get['page'] ?? 1));

        $limit = max(
            5,
            min(
                50,
                (int)$this->config->get('ohbono_wallet_history_limit') ?: 20
            )
        );

        $start = ($page - 1) * $limit;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_balance'] = $this->language->get('text_balance');
        $data['text_available'] = $this->language->get('text_available');
        $data['text_history'] = $this->language->get('text_history');
        $data['text_summary'] = $this->language->get('text_summary');
        $data['text_no_transactions'] = $this->language->get('text_no_transactions');
        $data['text_total_credited'] = $this->language->get('text_total_credited');
        $data['text_total_debited'] = $this->language->get('text_total_debited');
        $data['text_transaction_count'] = $this->language->get('text_transaction_count');

        $data['column_date'] = $this->language->get('column_date');
        $data['column_type'] = $this->language->get('column_type');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_balance'] = $this->language->get('column_balance');
        $data['column_order'] = $this->language->get('column_order');
        $data['column_action'] = $this->language->get('column_action');

        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');
        $data['button_view'] = $this->language->get('button_view');
        $data['button_back'] = $this->language->get('button_back');

        $data['balance'] = $this->model_extension_ohbono_account_wallet
            ->getBalance($customer_id);

        $data['summary'] = $this->model_extension_ohbono_account_wallet
            ->getSummary($customer_id);

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_account_wallet
            ->getTransactions($customer_id, $start, $limit) as $transaction) {

            $data['transactions'][] = [
                'transaction_id' => (int)$transaction['transaction_id'],
                'date' => $transaction['date_added'],
                'type' => ucwords(str_replace('_', ' ', $transaction['type'])),
                'direction' => $transaction['direction'],
                'reference' => $transaction['reference'],
                'amount' => $this->currency->format(
                    (float)$transaction['amount'],
                    $this->session->data['currency']
                ),
                'balance' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $this->session->data['currency']
                ),
                'order_id' => (int)$transaction['order_id'],
                'view' => $this->url->link(
                    'extension/ohbono/account/wallet.info',
                    'language=' . $this->config->get('config_language') .
                    '&transaction_id=' . (int)$transaction['transaction_id']
                )
            ];
        }

        $total = $this->model_extension_ohbono_account_wallet
            ->getTotalTransactions($customer_id);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/ohbono/account/wallet',
            'language=' . $this->config->get('config_language') .
            '&page={page}'
        );

        $data['pagination'] = $pagination->render();

        $data['account'] = $this->url->link(
            'account/account',
            'language=' . $this->config->get('config_language')
        );

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/account/wallet',
                $data
            )
        );
    }

    public function info(): void
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

        $transaction_id = (int)($this->request->get['transaction_id'] ?? 0);

        $transaction = $this->model_extension_ohbono_account_wallet
            ->getTransaction(
                (int)$this->customer->getId(),
                $transaction_id
            );

        if (!$transaction) {
            $this->response->redirect($this->url->link(
                'extension/ohbono/account/wallet',
                'language=' . $this->config->get('config_language')
            ));
            return;
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_details'] = $this->language->get('text_details');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');

        $data['label_transaction_id'] = $this->language->get('label_transaction_id');
        $data['label_date'] = $this->language->get('label_date');
        $data['label_type'] = $this->language->get('label_type');
        $data['label_direction'] = $this->language->get('label_direction');
        $data['label_amount'] = $this->language->get('label_amount');
        $data['label_before'] = $this->language->get('label_before');
        $data['label_after'] = $this->language->get('label_after');
        $data['label_reference'] = $this->language->get('label_reference');
        $data['label_order'] = $this->language->get('label_order');
        $data['label_comment'] = $this->language->get('label_comment');

        $data['transaction'] = [
            'transaction_id' => (int)$transaction['transaction_id'],
            'date' => $transaction['date_added'],
            'type' => ucwords(str_replace('_', ' ', $transaction['type'])),
            'direction' => $transaction['direction'],
            'amount' => $this->currency->format(
                (float)$transaction['amount'],
                $this->session->data['currency']
            ),
            'before' => $this->currency->format(
                (float)$transaction['balance_before'],
                $this->session->data['currency']
            ),
            'after' => $this->currency->format(
                (float)$transaction['balance_after'],
                $this->session->data['currency']
            ),
            'reference' => $transaction['reference'],
            'order_id' => (int)$transaction['order_id'],
            'comment' => $transaction['comment']
        ];

        $data['back'] = $this->url->link(
            'extension/ohbono/account/wallet',
            'language=' . $this->config->get('config_language')
        );

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/account/wallet_info',
                $data
            )
        );
    }
}
