<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

use Opencart\System\Library\Ohbono\WalletException;
use Opencart\System\Library\Ohbono\WalletFactory;
use Opencart\System\Library\Ohbono\WalletTransaction;

class WalletCustomer extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_customer');
        $this->load->model('extension/ohbono/module/wallet_customer');

        $page = max(1, (int)($this->request->get['page'] ?? 1));
        $limit = 20;
        $filter_customer = trim((string)($this->request->get['filter_customer'] ?? ''));
        $filter_status = isset($this->request->get['filter_status']) ? (int)$this->request->get['filter_status'] : -1;

        $start = ($page - 1) * $limit;

        $data['wallets'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_customer->getWallets([
            'filter_customer' => $filter_customer,
            'filter_status' => $filter_status,
            'start' => $start,
            'limit' => $limit
        ]) as $wallet) {
            $data['wallets'][] = [
                'wallet_id' => (int)$wallet['wallet_id'],
                'customer_id' => (int)$wallet['customer_id'],
                'customer' => trim($wallet['firstname'] . ' ' . $wallet['lastname']),
                'email' => $wallet['email'],
                'balance' => $this->currency->format(
                    (float)$wallet['balance'],
                    $this->config->get('config_currency')
                ),
                'status' => (int)$wallet['status'],
                'edit' => $this->url->link(
                    'extension/ohbono/module/wallet_customer.form',
                    'user_token=' . $this->session->data['user_token'] . '&customer_id=' . (int)$wallet['customer_id']
                )
            ];
        }

        $total = $this->model_extension_ohbono_module_wallet_customer->getTotalWallets([
            'filter_customer' => $filter_customer,
            'filter_status' => $filter_status
        ]);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token'] .
            '&page={page}' .
            ($filter_customer !== '' ? '&filter_customer=' . urlencode($filter_customer) : '') .
            ($filter_status !== -1 ? '&filter_status=' . $filter_status : '')
        );

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            $total ? $start + 1 : 0,
            min($start + $limit, $total),
            $total,
            $total ? ceil($total / $limit) : 1
        );

        $data['filter_customer'] = $filter_customer;
        $data['filter_status'] = $filter_status;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all'] = $this->language->get('text_all');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_email'] = $this->language->get('column_email');
        $data['column_balance'] = $this->language->get('column_balance');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_action'] = $this->language->get('column_action');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_view'] = $this->language->get('button_view');

        $data['filter_url'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet_customer_list', $data)
        );
    }

    public function form(): void
    {
        $this->load->language('extension/ohbono/module/wallet_customer');
        $this->load->model('extension/ohbono/module/wallet_customer');

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);

        if ($customer_id <= 0) {
            $this->response->redirect($this->url->link(
                'extension/ohbono/module/wallet_customer',
                'user_token=' . $this->session->data['user_token']
            ));

            return;
        }

        $customer = $this->model_extension_ohbono_module_wallet_customer->getCustomer($customer_id);
        $wallet = $this->model_extension_ohbono_module_wallet_customer->getWallet($customer_id);

        if (!$customer || !$wallet) {
            $this->session->data['error'] = $this->language->get('error_wallet_not_found');

            $this->response->redirect($this->url->link(
                'extension/ohbono/module/wallet_customer',
                'user_token=' . $this->session->data['user_token']
            ));

            return;
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_details'] = $this->language->get('text_details');
        $data['text_transactions'] = $this->language->get('text_transactions');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');
        $data['entry_amount'] = $this->language->get('entry_amount');
        $data['entry_comment'] = $this->language->get('entry_comment');
        $data['entry_reference'] = $this->language->get('entry_reference');
        $data['button_credit'] = $this->language->get('button_credit');
        $data['button_debit'] = $this->language->get('button_debit');
        $data['button_back'] = $this->language->get('button_back');
        $data['column_date'] = $this->language->get('column_date');
        $data['column_type'] = $this->language->get('column_type');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_balance'] = $this->language->get('column_balance');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_comment'] = $this->language->get('column_comment');

        $data['customer_id'] = $customer_id;
        $data['customer'] = trim($customer['firstname'] . ' ' . $customer['lastname']);
        $data['email'] = $customer['email'];
        $data['balance'] = $this->currency->format(
            (float)$wallet['balance'],
            $this->config->get('config_currency')
        );

        $data['credit_action'] = $this->url->link(
            'extension/ohbono/module/wallet_customer.credit',
            'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $customer_id
        );

        $data['debit_action'] = $this->url->link(
            'extension/ohbono/module/wallet_customer.debit',
            'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $customer_id
        );

        $data['back'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $this->session->data['user_token']
        );

        $data['transactions'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_customer->getTransactions($customer_id, 0, 50) as $transaction) {
            $data['transactions'][] = [
                'date' => $transaction['date_added'],
                'type' => ucwords(str_replace('_', ' ', $transaction['type'])),
                'amount' => ($transaction['direction'] === 'credit' ? '+' : '-') .
                    $this->currency->format((float)$transaction['amount'], $this->config->get('config_currency')),
                'balance' => $this->currency->format((float)$transaction['balance_after'], $this->config->get('config_currency')),
                'reference' => $transaction['reference'],
                'comment' => $transaction['comment'],
                'direction' => $transaction['direction']
            ];
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet_customer_form', $data)
        );
    }

    public function credit(): void
    {
        $this->changeBalance(true);
    }

    public function debit(): void
    {
        $this->changeBalance(false);
    }

    private function changeBalance(bool $credit): void
    {
        $this->load->language('extension/ohbono/module/wallet_customer');

        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet_customer')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->redirectToForm();

            return;
        }

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);
        $amount = (float)($this->request->post['amount'] ?? 0);
        $comment = trim((string)($this->request->post['comment'] ?? ''));
        $reference = trim((string)($this->request->post['reference'] ?? ''));

        if ($customer_id <= 0 || $amount <= 0) {
            $this->session->data['error'] = $this->language->get('error_amount');
            $this->redirectToForm($customer_id);

            return;
        }

        try {
            $this->load->library('ohbono/WalletFactory');

            $factory = new WalletFactory($this->registry);
            $service = $factory->service();

            if ($credit) {
                $service->credit(
                    $customer_id,
                    $amount,
                    WalletTransaction::TYPE_ADMIN_CREDIT,
                    $comment,
                    $reference,
                    0,
                    (int)$this->user->getId()
                );

                $this->session->data['success'] = $this->language->get('text_credit_success');
            } else {
                $service->debit(
                    $customer_id,
                    $amount,
                    WalletTransaction::TYPE_ADMIN_DEBIT,
                    $comment,
                    $reference,
                    0,
                    (int)$this->user->getId()
                );

                $this->session->data['success'] = $this->language->get('text_debit_success');
            }
        } catch (WalletException $e) {
            $this->session->data['error'] = $e->getMessage();
        } catch (\Throwable $e) {
            $this->session->data['error'] = $this->language->get('error_operation');
        }

        $this->redirectToForm($customer_id);
    }

    private function redirectToForm(int $customer_id = 0): void
    {
        $url = 'extension/ohbono/module/wallet_customer';

        if ($customer_id > 0) {
            $url .= '.form&customer_id=' . $customer_id;
        }

        $this->response->redirect(
            $this->url->link(
                $url,
                'user_token=' . $this->session->data['user_token']
            )
        );
    }
}
