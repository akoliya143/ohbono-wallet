<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletEvent extends \Opencart\System\Engine\Model
{
    public function getEventDefinitions(): array
    {
        return [
            [
                'code' => 'ohbono_wallet_catalog_checkout_before',
                'trigger' => 'catalog/controller/checkout/*/before',
                'action' =>
                    'extension/ohbono/module/wallet_event/checkoutBefore'
            ],
            [
                'code' => 'ohbono_wallet_catalog_order_after',
                'trigger' => 'catalog/model/checkout/order/addOrder/after',
                'action' =>
                    'extension/ohbono/module/wallet_event/orderAfter'
            ]
        ];
    }
}
