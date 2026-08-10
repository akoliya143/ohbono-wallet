<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletTransaction extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_transaction'
        );

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_transaction'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_transaction'
        );

        $customer_id = (int)(
            $this->request->get['customer_id'] ?? 0
        );

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['transactions'] =
            $this->model_extension_ohbono_module_wallet_transaction
                ->getTransactions(
                    $customer_id,
                    0,
                    100
                );

        $data['customer_id'] = $customer_id;

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_transaction',
                $data
            )
        );
    }
}
