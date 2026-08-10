<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletRelease extends \Opencart\System\Engine\Controller
{
    public function check(): void
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
            'schema' =>
                $this->request->get['schema'] ?? 0,
            'permissions' =>
                $this->request->get['permissions'] ?? 0,
            'idempotency' =>
                $this->request->get['idempotency'] ?? 0,
            'refund' =>
                $this->request->get['refund'] ?? 0,
            'journal' =>
                $this->request->get['journal'] ?? 0,
            'wallet_only' =>
                $this->request->get['wallet_only'] ?? 0,
            'partial' =>
                $this->request->get['partial'] ?? 0,
            'failure' =>
                $this->request->get['failure'] ?? 0,
            'backup' =>
                $this->request->get['backup'] ?? 0,
            'ledger' =>
                $this->request->get['ledger'] ?? 0,
            'cross_customer' =>
                $this->request->get['cross_customer'] ?? 0
        ];

        $required = array_keys($checks);
        $missing = [];

        foreach ($required as $key) {
            if (empty($checks[$key])) {
                $missing[] = $key;
            }
        }

        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode([
                'ready' => !$missing,
                'missing' => $missing,
                'checked_at' => date('c')
            ])
        );
    }
}
