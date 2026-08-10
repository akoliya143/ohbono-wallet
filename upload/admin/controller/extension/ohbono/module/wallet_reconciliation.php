<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletReconciliation extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_reconciliation'
        );

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_reconciliation'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_reconciliation'
        );

        $data['heading_title'] =
            $this->language->get('heading_title');
        $data['text_no_results'] =
            $this->language->get('text_no_results');

        $data['orders'] =
            $this->model_extension_ohbono_module_wallet_reconciliation
                ->getOrdersRequiringReconciliation(
                    0,
                    200
                );

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_reconciliation',
                $data
            )
        );
    }
}
