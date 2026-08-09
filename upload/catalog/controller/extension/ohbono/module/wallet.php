<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet');

        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login'));
            return;
        }

        if (!$this->config->get('ohbono_wallet_status')) {
            $this->response->redirect($this->url->link('account/account'));
            return;
        }

        $this->load->model('extension/ohbono/module/wallet');

        $customer_id = (int)$this->customer->getId();
        $currency = $this->session->data['currency'];

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_available_balance'] = $this->language->get('text_available_balance');
        $data['text_recent_transactions'] = $this->language->get('text_recent_transactions');
        $data['text_refund_summary'] = $this->language->get('text_refund_summary');
        $data['text_total_refunded'] = $this->language->get('text_total_refunded');
        $data['text_refund_count'] = $this->language->get('text_refund_count');
        $data['text_no_transactions'] = $this->language->get('text_no_transactions');
        $data['text_view_history'] = $this->language->get('text_view_history');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');
        $data['text_refund'] = $this->language->get('text_refund');
        $data['text_order'] = $this->language->get('text_order');

        $balance = $this->model_extension_ohbono_module_wallet
            ->getBalance($customer_id);

        $data['balance'] = $this->currency->format(
            $balance,
            $currency
        );

        $maximum = (float)$this->config->get('ohbono_wallet_maximum_use');

        $available = $balance;

        if ($maximum > 0) {
            $available = min($balance, $maximum);
        }

        $data['available'] = $this->currency->format(
            max(0, $available),
            $currency
        );

        $transactions = $this->model_extension_ohbono_module_wallet
            ->getTransactions($customer_id, 0, 5);

        $data['transactions'] = [];

        foreach ($transactions as $transaction) {
            $data['transactions'][] = [
                'date' => $transaction['date_added'],
                'type' => $transaction['type'],
                'direction' => $transaction['direction'],
                'amount' => $this->currency->format(
                    (float)$transaction['amount'],
                    $currency
                ),
                'balance' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $currency
                ),
                'reference' => $transaction['reference'],
                'order_id' => (int)$transaction['order_id']
            ];
        }

        $refund_summary = $this->model_extension_ohbono_module_wallet
            ->getRefundSummary($customer_id);

        $data['refund_summary'] = [
            'total' => $this->currency->format(
                (float)$refund_summary['total'],
                $currency
            ),
            'count' => (int)$refund_summary['count']
        ];

        $data['history'] = $this->url->link(
            'extension/ohbono/module/wallet.history'
        );

        $data['continue'] = $this->url->link('account/account');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_dashboard',
                $data
            )
        );
    }
}
