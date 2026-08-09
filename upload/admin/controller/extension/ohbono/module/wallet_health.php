<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletHealth extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_health');

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_health'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->library('ohbono/integrity');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_health'] = $this->language->get('text_health');
        $data['text_healthy'] = $this->language->get('text_healthy');
        $data['text_attention'] = $this->language->get('text_attention');

        $data['label_wallets'] = $this->language->get('label_wallets');
        $data['label_customers_with_balance'] =
            $this->language->get('label_customers_with_balance');
        $data['label_transactions'] =
            $this->language->get('label_transactions');
        $data['label_balance_total'] =
            $this->language->get('label_balance_total');
        $data['label_ledger_total'] =
            $this->language->get('label_ledger_total');
        $data['label_difference'] =
            $this->language->get('label_difference');
        $data['label_duplicates'] =
            $this->language->get('label_duplicates');
        $data['label_orphans'] =
            $this->language->get('label_orphans');

        $data['text_unhealthy_customers'] =
            $this->language->get('text_unhealthy_customers');

        $data['overview'] = $this->wallet_integrity->getOverview();
        $data['unhealthy_customers'] =
            $this->wallet_integrity->getUnhealthyCustomers();

        $data['currency'] = $this->config->get('config_currency');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_health',
                $data
            )
        );
    }
}
