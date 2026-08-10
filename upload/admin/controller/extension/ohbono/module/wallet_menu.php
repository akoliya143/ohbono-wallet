<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletMenu extends \Opencart\System\Engine\Controller
{
    /**
     * Returns menu metadata for the OpenCart admin integration layer.
     *
     * Actual menu registration should be wired through the extension's
     * OpenCart event/extension mechanism rather than modifying core files.
     */
    public function metadata(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_reconciliation'
        )) {
            $this->json([
                'success' => false
            ]);
            return;
        }

        $this->json([
            'success' => true,
            'items' => [
                [
                    'route' =>
                        'extension/ohbono/module/wallet_reconciliation',
                    'title' =>
                        'Wallet Reconciliation'
                ],
                [
                    'route' =>
                        'extension/ohbono/module/wallet_refund',
                    'title' =>
                        'Wallet Refund'
                ],
                [
                    'route' =>
                        'extension/ohbono/module/wallet_diagnostics',
                    'title' =>
                        'Wallet Diagnostics'
                ]
            ]
        ]);
    }

    private function json(array $data): void
    {
        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode($data)
        );
    }
}
