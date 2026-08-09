<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletOrder extends \Opencart\System\Engine\Controller
{
    public function info(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_order'
        )) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Permission denied.'
            ]));
            return;
        }

        $order_id = (int)($this->request->get['order_id'] ?? 0);

        $this->load->model('extension/ohbono/module/wallet_order');

        $rows = $this->model_extension_ohbono_module_wallet_order
            ->getOrderWalletTransactions($order_id);

        $this->response->addHeader('Content-Type: application/json');

        $this->response->setOutput(json_encode([
            'success' => true,
            'order_id' => $order_id,
            'transactions' => $rows
        ]));
    }
}
