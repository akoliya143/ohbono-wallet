<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

/**
 * Event installer helper.
 *
 * The exact event APIs vary with the OpenCart 4.1.x build/extension manager,
 * so this class exposes definitions separately from registration. The
 * installation controller can map these definitions to the site's supported
 * event-registration mechanism.
 */
class WalletEventInstaller extends \Opencart\System\Engine\Model
{
    public function getDefinitions(): array
    {
        return [
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
                'trigger' => 'catalog/model/checkout/order/addOrder/after',
                'action' =>
                    'extension/ohbono/module/wallet_event/orderAfter',
                'status' => 1,
                'sort_order' => 100
            ]
        ];
    }
}
