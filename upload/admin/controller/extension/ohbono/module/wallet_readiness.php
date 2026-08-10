<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletReadiness extends \Opencart\System\Engine\Controller
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

        $checks = [
            'database_schema' =>
                $this->request->get['schema'] ?? 0,
            'admin_permissions' =>
                $this->request->get['permissions'] ?? 0,
            'wallet_capture_idempotency' =>
                $this->request->get['idempotency'] ?? 0,
            'refund_reversal' =>
                $this->request->get['refund'] ?? 0,
            'journal_checkout_verified' =>
                $this->request->get['journal'] ?? 0,
            'staging_wallet_only' =>
                $this->request->get['wallet_only'] ?? 0,
            'staging_partial_wallet' =>
                $this->request->get['partial'] ?? 0,
            'staging_failure_reconciliation' =>
                $this->request->get['failure'] ?? 0,
            'production_backup' =>
                $this->request->get['backup'] ?? 0
        ];

        $gate =
            new \OhbonoWalletReadinessGate();

        $result = $gate->evaluate($checks);

        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode($result)
        );
    }
}
