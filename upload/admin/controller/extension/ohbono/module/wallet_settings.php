<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletSettings extends \Opencart\System\Engine\Controller
{
    public function install(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet_settings')) {
            return;
        }

        $this->load->model('setting/setting');

        $defaults = [
            'ohbono_wallet_status' => 1,
            'ohbono_wallet_allow_checkout' => 1,
            'ohbono_wallet_allow_negative' => 0,
            'ohbono_wallet_minimum_use' => 0,
            'ohbono_wallet_maximum_use' => 0,
            'ohbono_wallet_sort_order' => 5,
            'ohbono_wallet_history_limit' => 20
        ];

        $this->model_setting_setting->editSetting('ohbono_wallet', $defaults);
    }

    public function uninstall(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet_settings')) {
            return;
        }

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('ohbono_wallet');
    }
}
