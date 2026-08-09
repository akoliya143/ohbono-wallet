<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletDashboard extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_dashboard');
        $this->load->model('extension/ohbono/module/wallet_dashboard');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_dashboard'] = $this->language->get('text_dashboard');
        $data['text_total_balance'] = $this->language->get('text_total_balance');
        $data['text_customers'] = $this->language->get('text_customers');
        $data['text_total_credits'] = $this->language->get('text_total_credits');
        $data['text_total_debits'] = $this->language->get('text_total_debits');
        $data['text_recent'] = $this->language->get('text_recent');

        $data['column_date'] = $this->language->get('column_date');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_type'] = $this->language->get('column_type');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_balance'] = $this->language->get('column_balance');

        $stats = $this->model_extension_ohbono_module_wallet_dashboard->getStats();

        $data['total_balance'] = $this->currency->format(
            $stats['total_balance'],
            $this->config->get('config_currency')
        );

        $data['customer_count'] = $stats['customer_count'];

        $data['total_credits'] = $this->currency->format(
            $stats['total_credits'],
            $this->config->get('config_currency')
        );

        $data['total_debits'] = $this->currency->format(
            $stats['total_debits'],
            $this->config->get('config_currency')
        );

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_dashboard->getRecentTransactions(10) as $transaction) {
            $data['transactions'][] = [
                'date' => $transaction['date_added'],
                'customer' => trim($transaction['firstname'] . ' ' . $transaction['lastname'])
                    ?: ('Customer #' . (int)$transaction['customer_id']),
                'type' => ucwords(str_replace('_', ' ', $transaction['type'])),
                'direction' => $transaction['direction'],
                'amount' => $this->currency->format(
                    (float)$transaction['amount'],
                    $this->config->get('config_currency')
                ),
                'balance' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $this->config->get('config_currency')
                )
            ];
        }

        $data['transactions_url'] = $this->url->link(
            'extension/ohbono/module/wallet_transaction',
            'user_token=' . $this->session->data['user_token']
        );

        $data['customers_url'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet_dashboard', $data)
        );
    }
}
