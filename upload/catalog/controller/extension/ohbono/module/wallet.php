<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function history(): void
    {
        $this->load->language('extension/ohbono/module/wallet');

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        $this->load->model('extension/ohbono/module/wallet');

        $limit = (int)$this->config->get('ohbono_wallet_history_limit');

        if ($limit < 5 || $limit > 100) {
            $limit = 20;
        }

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['text_wallet_history'] =
            $this->language->get('text_wallet_history');

        $data['text_refund'] =
            $this->language->get('text_refund');

        $data['text_credit'] =
            $this->language->get('text_credit');

        $data['text_debit'] =
            $this->language->get('text_debit');

        $data['text_no_transactions'] =
            $this->language->get('text_no_transactions');

        $data['column_date'] =
            $this->language->get('column_date');

        $data['column_type'] =
            $this->language->get('column_type');

        $data['column_amount'] =
            $this->language->get('column_amount');

        $data['column_balance'] =
            $this->language->get('column_balance');

        $data['column_reference'] =
            $this->language->get('column_reference');

        $data['column_order'] =
            $this->language->get('column_order');

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_module_wallet
            ->getTransactions(
                (int)$this->customer->getId(),
                0,
                $limit
            ) as $transaction) {

            $data['transactions'][] = [
                'date' => $transaction['date_added'],
                'type' => $transaction['type'],
                'direction' => $transaction['direction'],
                'amount' => $this->currency->format(
                    (float)$transaction['amount'],
                    $this->session->data['currency']
                ),
                'balance' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $this->session->data['currency']
                ),
                'reference' => $transaction['reference'],
                'order_id' => (int)$transaction['order_id']
            ];
        }

        $data['refunds'] = [];

        foreach ($this->model_extension_ohbono_module_wallet
            ->getRefundTransactions(
                (int)$this->customer->getId(),
                $limit
            ) as $refund) {

            $data['refunds'][] = [
                'date' => $refund['date_added'],
                'amount' => $this->currency->format(
                    (float)$refund['amount'],
                    $this->session->data['currency']
                ),
                'reference' => $refund['reference'],
                'order_id' => (int)$refund['order_id'],
                'transaction_id' => (int)$refund['transaction_id']
            ];
        }

        $data['continue'] = $this->url->link('account/account');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_history',
                $data
            )
        );
    }
}
