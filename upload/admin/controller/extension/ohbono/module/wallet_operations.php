<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletOperations extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_operations'
        )) {
            $this->response->setOutput(
                'Permission denied.'
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_operations'
        );

        $data['operations'] =
            $this->model_extension_ohbono_module_wallet_operations
                ->getRecent(0, 200);

        $data['heading_title'] =
            'OHBONO Wallet Operations';

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_operations',
                $data
            )
        );
    }
}
