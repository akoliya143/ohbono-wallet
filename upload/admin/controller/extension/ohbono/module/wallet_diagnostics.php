<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletDiagnostics extends \Opencart\System\Engine\Controller
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

        $validator =
            new \OhbonoWalletSchemaValidator(
                $this->db
            );

        $result = $validator->validate();

        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode($result)
        );
    }
}
