<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletAudit extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_audit');

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_audit'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->model('extension/ohbono/module/wallet_audit');

        $page = max(1, (int)($this->request->get['page'] ?? 1));
        $limit = 50;
        $start = ($page - 1) * $limit;

        $filters = $this->getFilters();

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_credit'] = $this->language->get('text_credit');
        $data['text_debit'] = $this->language->get('text_debit');
        $data['text_no_results'] = $this->language->get('text_no_results');

        $data['column_date'] = $this->language->get('column_date');
        $data['column_customer'] = $this->language->get('column_customer');
        $data['column_admin'] = $this->language->get('column_admin');
        $data['column_action'] = $this->language->get('column_action');
        $data['column_amount'] = $this->language->get('column_amount');
        $data['column_before'] = $this->language->get('column_before');
        $data['column_after'] = $this->language->get('column_after');
        $data['column_reason'] = $this->language->get('column_reason');
        $data['column_reference'] = $this->language->get('column_reference');

        $data['entry_customer_id'] =
            $this->language->get('entry_customer_id');
        $data['entry_admin_id'] =
            $this->language->get('entry_admin_id');
        $data['entry_action'] =
            $this->language->get('entry_action');
        $data['entry_date_start'] =
            $this->language->get('entry_date_start');
        $data['entry_date_end'] =
            $this->language->get('entry_date_end');

        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');
        $data['button_export'] = $this->language->get('button_export');

        $data['audits'] = [];

        foreach ($this->model_extension_ohbono_module_wallet_audit
            ->getAudits($filters, $start, $limit) as $audit) {

            $data['audits'][] = [
                'audit_id' => (int)$audit['audit_id'],
                'date' => $audit['date_added'],
                'customer_id' => (int)$audit['customer_id'],
                'admin_id' => (int)$audit['admin_user_id'],
                'admin_name' => trim((string)$audit['admin_name']),
                'action' => $audit['action'],
                'direction' => $this->getDirection($audit['action']),
                'amount' => $this->currency->format(
                    (float)$audit['amount'],
                    $this->config->get('config_currency')
                ),
                'before' => $this->currency->format(
                    (float)$audit['balance_before'],
                    $this->config->get('config_currency')
                ),
                'after' => $this->currency->format(
                    (float)$audit['balance_after'],
                    $this->config->get('config_currency')
                ),
                'reference' => $audit['reference'],
                'reason' => $audit['reason']
            ];
        }

        $total = $this->model_extension_ohbono_module_wallet_audit
            ->getTotalAudits($filters);

        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/ohbono/module/wallet_audit',
            'user_token=' . $this->session->data['user_token'] .
            $this->buildQuery($filters) .
            '&page={page}'
        );

        $data['pagination'] = $pagination->render();

        $data['filters'] = $filters;

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_audit',
            'user_token=' . $this->session->data['user_token']
        );

        $data['export'] = $this->url->link(
            'extension/ohbono/module/wallet_audit.export',
            'user_token=' . $this->session->data['user_token'] .
            $this->buildQuery($filters)
        );

        $data['reset'] = $this->url->link(
            'extension/ohbono/module/wallet_audit',
            'user_token=' . $this->session->data['user_token']
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_audit',
                $data
            )
        );
    }

    public function export(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_audit'
        )) {
            $this->response->setOutput('Permission denied.');
            return;
        }

        $this->load->model('extension/ohbono/module/wallet_audit');

        $filters = $this->getFilters();

        $rows = $this->model_extension_ohbono_module_wallet_audit
            ->getAudits($filters, 0, 10000);

        $filename = 'ohbono-wallet-audit-' . date('Y-m-d-His') . '.csv';

        $this->response->addHeader(
            'Content-Type: text/csv; charset=utf-8'
        );
        $this->response->addHeader(
            'Content-Disposition: attachment; filename="' . $filename . '"'
        );
        $this->response->addHeader('Pragma: no-cache');
        $this->response->addHeader('Expires: 0');

        $output = fopen('php://temp', 'w+');

        fputcsv($output, [
            'Audit ID',
            'Date',
            'Customer ID',
            'Admin User ID',
            'Admin Name',
            'Action',
            'Amount',
            'Balance Before',
            'Balance After',
            'Reference',
            'Reason',
            'IP Address',
            'User Agent',
            'Transaction ID'
        ]);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['audit_id'],
                $row['date_added'],
                $row['customer_id'],
                $row['admin_user_id'],
                $row['admin_name'],
                $row['action'],
                $row['amount'],
                $row['balance_before'],
                $row['balance_after'],
                $row['reference'],
                $row['reason'],
                $row['ip_address'],
                $row['user_agent'],
                $row['transaction_id']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $this->response->setOutput($csv);
    }

    private function getFilters(): array
    {
        return [
            'customer_id' => max(
                0,
                (int)($this->request->get['filter_customer_id'] ?? 0)
            ),
            'admin_id' => max(
                0,
                (int)($this->request->get['filter_admin_id'] ?? 0)
            ),
            'action' => trim(
                (string)($this->request->get['filter_action'] ?? '')
            ),
            'date_start' => trim(
                (string)($this->request->get['filter_date_start'] ?? '')
            ),
            'date_end' => trim(
                (string)($this->request->get['filter_date_end'] ?? '')
            )
        ];
    }

    private function buildQuery(array $filters): string
    {
        $query = '';

        if ($filters['customer_id']) {
            $query .= '&filter_customer_id=' .
                (int)$filters['customer_id'];
        }

        if ($filters['admin_id']) {
            $query .= '&filter_admin_id=' .
                (int)$filters['admin_id'];
        }

        if ($filters['action'] !== '') {
            $query .= '&filter_action=' .
                urlencode($filters['action']);
        }

        if ($filters['date_start'] !== '') {
            $query .= '&filter_date_start=' .
                urlencode($filters['date_start']);
        }

        if ($filters['date_end'] !== '') {
            $query .= '&filter_date_end=' .
                urlencode($filters['date_end']);
        }

        return $query;
    }

    private function getDirection(string $action): string
    {
        return str_contains($action, 'credit')
            ? 'credit'
            : 'debit';
    }
}
