<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletHistory extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_history');

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        if (!$this->config->get('ohbono_wallet_status')) {
            $this->response->redirect(
                $this->url->link('account/account')
            );
            return;
        }

        $this->load->model('extension/ohbono/module/wallet_history');

        $page = max(
            1,
            (int)($this->request->get['page'] ?? 1)
        );

        $limit = 20;
        $start = ($page - 1) * $limit;

        $customer_id = (int)$this->customer->getId();

        $transactions =
            $this->model_extension_ohbono_module_wallet_history
                ->getTransactions(
                    $customer_id,
                    $start,
                    $limit
                );

        $total =
            $this->model_extension_ohbono_module_wallet_history
                ->getTotalTransactions($customer_id);

        $currency = $this->session->data['currency'];

        $data = [];

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['text_date'] =
            $this->language->get('text_date');
        $data['text_type'] =
            $this->language->get('text_type');
        $data['text_amount'] =
            $this->language->get('text_amount');
        $data['text_balance'] =
            $this->language->get('text_balance');
        $data['text_reference'] =
            $this->language->get('text_reference');
        $data['text_order'] =
            $this->language->get('text_order');
        $data['text_credit'] =
            $this->language->get('text_credit');
        $data['text_debit'] =
            $this->language->get('text_debit');
        $data['text_refund'] =
            $this->language->get('text_refund');
        $data['text_no_transactions'] =
            $this->language->get('text_no_transactions');

        $data['transactions'] = [];

        foreach ($transactions as $transaction) {
            $type = (string)$transaction['type'];

            if ($type === 'order_refund') {
                $label = $data['text_refund'];
            } elseif ($transaction['direction'] === 'credit') {
                $label = $data['text_credit'];
            } else {
                $label = $data['text_debit'];
            }

            $data['transactions'][] = [
                'transaction_id' =>
                    (int)$transaction['transaction_id'],
                'date' => $transaction['date_added'],
                'type' => $label,
                'direction' =>
                    (string)$transaction['direction'],
                'amount' => $this->currency->format(
                    (float)$transaction['amount'],
                    $currency
                ),
                'balance' => $this->currency->format(
                    (float)$transaction['balance_after'],
                    $currency
                ),
                'reference' =>
                    (string)$transaction['reference'],
                'order_id' =>
                    (int)$transaction['order_id']
            ];
        }

        $data['pagination'] = '';

        if ($total > $limit) {
            $pagination =
                new \Opencart\System\Library\Pagination();

            $pagination->total = $total;
            $pagination->page = $page;
            $pagination->limit = $limit;

            $pagination->url = $this->url->link(
                'extension/ohbono/module/wallet_history',
                'page={page}'
            );

            $data['pagination'] = $pagination->render();
        }

        $data['back'] = $this->url->link(
            'extension/ohbono/module/wallet'
        );

        $data['header'] =
            $this->load->controller('common/header');
        $data['footer'] =
            $this->load->controller('common/footer');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['column_right'] =
            $this->load->controller('common/column_right');
        $data['content_top'] =
            $this->load->controller('common/content_top');
        $data['content_bottom'] =
            $this->load->controller('common/content_bottom');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_history',
                $data
            )
        );
    }
}
