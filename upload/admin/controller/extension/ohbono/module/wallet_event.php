<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletEvent extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_reconciliation'
        )) {
            $this->response->setOutput(
                'Permission denied.'
            );
            return;
        }

        $this->response->setOutput(
            'OHBONO Wallet event integration is installed.'
        );
    }
}
