<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletEventInstall extends \Opencart\System\Engine\Controller
{
    public function install(): void
    {
        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_event_install'
        )) {
            $this->response->setOutput(
                'Permission denied.'
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_event_registry'
        );

        $definitions = [
            [
                'code' => 'ohbono_wallet_checkout_before',
                'trigger' => 'catalog/controller/checkout/*/before',
                'action' =>
                    'extension/ohbono/module/wallet_event/checkoutBefore',
                'status' => 1,
                'sort_order' => 100
            ],
            [
                'code' => 'ohbono_wallet_order_after',
                'trigger' =>
                    'catalog/model/checkout/order/addOrder/after',
                'action' =>
                    'extension/ohbono/module/wallet_event/orderAfter',
                'status' => 1,
                'sort_order' => 100
            ]
        ];

        $ids =
            $this->model_extension_ohbono_module_wallet_event_registry
                ->register($definitions);

        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode([
                'success' => true,
                'registered_event_ids' => $ids
            ])
        );
    }
}
