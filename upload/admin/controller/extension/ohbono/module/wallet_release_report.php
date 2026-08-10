<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletReleaseReport extends \Opencart\System\Engine\Controller
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

        $results = [];

        foreach ([
            'wallet_only',
            'partial_wallet_external_success',
            'partial_wallet_external_failure',
            'insufficient_wallet_balance',
            'wallet_above_order_total',
            'duplicate_callback',
            'browser_refresh',
            'refund_after_paid_order',
            'reversal_after_failed_external_payment',
            'cross_customer_order_protection',
            'ledger_reconciliation',
            'journal_checkout'
        ] as $scenario) {
            $results[$scenario] =
                $this->request->get[$scenario] ?? null;
        }

        $report =
            new \OhbonoWalletReleaseReport();

        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode(
                $report->build($results)
            )
        );
    }
}
