<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletEvidence extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_diagnostics'
        )) {
            $this->response->setOutput(
                'Permission denied.'
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_evidence'
        );

        $data['results'] =
            $this->model_extension_ohbono_module_wallet_evidence
                ->getLatest();

        $data['heading_title'] =
            'OHBONO Wallet Staging Evidence';

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_evidence',
                $data
            )
        );
    }
}
