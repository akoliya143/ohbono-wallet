<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletAudit extends \Opencart\System\Engine\Controller
{
    public function summary(): void
    {
        $this->response->addHeader('Content-Type: application/json');

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_audit'
        )) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Permission denied.'
            ]));
            return;
        }

        $this->load->model('extension/ohbono/module/wallet_audit');

        $summary =
            $this->model_extension_ohbono_module_wallet_audit
                ->getSummary();

        $this->response->setOutput(json_encode([
            'success' => true,
            'summary' => $summary
        ]));
    }
}
