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

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_balance'] = $this->language->get('text_balance');
        $data['text_available'] = $this->language->get('text_available');
        $data['text_history'] = $this->language->get('text_history');
        $data['text_no_transactions'] = $this->language->get('text_no_transactions');

        $data['column_date'] = $this->language->get('column_date');
        $data['column_type'] = $this->language->get('column_type');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_balance'] = $this->language->get('column_balance');
        $data['column_order'] = $this->language->get('column_order');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');

        $data['balance'] = $this->model_extension_ohbono_account_wallet
            ->getBalance($customer_id);

        $limit = max(
            5,
            min(
                100,
                (int)$this->config->get('ohbono_wallet_history_limit') ?: 20
            )
        );

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_account_wallet
            ->getTransactions($customer_id, 0, $limit) as $transaction) {

            $data['transactions'][] = [
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
                'order_id' => (int)$transaction['order_id']
            ];
        }

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
}
