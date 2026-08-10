<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletHistory extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_history'
        );

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_history'
        );

        $customer_id = (int)$this->customer->getId();

        $page = max(
            1,
            (int)($this->request->get['page'] ?? 1)
        );

        $limit = 20;
        $start = ($page - 1) * $limit;

        $total =
            $this->model_extension_ohbono_module_wallet_history
                ->getTotalTransactions($customer_id);

        $transactions =
            $this->model_extension_ohbono_module_wallet_history
                ->getTransactions(
                    $customer_id,
                    $start,
                    $limit
                );

        $data['heading_title'] =
            $this->language->get('heading_title');
        $data['text_no_results'] =
            $this->language->get('text_no_results');
        $data['text_credit'] =
            $this->language->get('text_credit');
        $data['text_debit'] =
            $this->language->get('text_debit');
        $data['text_view'] =
            $this->language->get('text_view');
        $data['text_page'] =
            $this->language->get('text_page');

        $data['transactions'] = $transactions;
        $data['page'] = $page;
        $data['pages'] = max(1, (int)ceil($total / $limit));

        $data['transaction_links'] = [];

        foreach ($transactions as $transaction) {
            $data['transaction_links'][
                (int)$transaction['transaction_id']
            ] = $this->url->link(
                'extension/ohbono/module/wallet_history.info',
                'transaction_id=' .
                (int)$transaction['transaction_id']
            );
        }

        $data['pagination'] = '';

        if ($total > $limit) {
            $pagination = new \Opencart\System\Library\Pagination();
            $pagination->total = $total;
            $pagination->page = $page;
            $pagination->limit = $limit;
            $pagination->url = $this->url->link(
                'extension/ohbono/module/wallet_history',
                'page={page}'
            );

            $data['pagination'] =
                $pagination->render();
        }

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

    public function info(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_history'
        );

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        $transaction_id = (int)(
            $this->request->get['transaction_id'] ?? 0
        );

        $this->load->model(
            'extension/ohbono/module/wallet_history'
        );

        $data['transaction'] =
            $this->model_extension_ohbono_module_wallet_history
                ->getTransaction(
                    (int)$this->customer->getId(),
                    $transaction_id
                );

        if (!$data['transaction']) {
            $this->response->redirect(
                $this->url->link(
                    'extension/ohbono/module/wallet_history'
                )
            );
            return;
        }

        $data['heading_title'] =
            $this->language->get('text_transaction_detail');
        $data['text_credit'] =
            $this->language->get('text_credit');
        $data['text_debit'] =
            $this->language->get('text_debit');

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
                'extension/ohbono/module/wallet_transaction_info',
                $data
            )
        );
    }
}
