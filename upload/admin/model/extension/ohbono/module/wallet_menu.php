<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletMenu extends \Opencart\System\Engine\Model
{
    public function getMenuItems(): array
    {
        return [
            [
                'name' => 'OHBONO Wallet',
                'route' => 'extension/ohbono/module/wallet_dashboard',
                'icon' => 'fa-solid fa-wallet'
            ],
            [
                'name' => 'Wallet Customers',
                'route' => 'extension/ohbono/module/wallet_customer',
                'icon' => 'fa-solid fa-users'
            ],
            [
                'name' => 'Wallet Settings',
                'route' => 'extension/ohbono/module/wallet_settings',
                'icon' => 'fa-solid fa-gear'
            ],
            [
                'name' => 'Wallet Payment',
                'route' => 'extension/ohbono/payment/wallet',
                'icon' => 'fa-solid fa-credit-card'
            ]
        ];
    }
}
