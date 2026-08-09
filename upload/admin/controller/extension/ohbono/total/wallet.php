<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Total;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function install(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/total/wallet')) {
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting('total_wallet', [
            'total_wallet_status' => 1,
            'total_wallet_allow_checkout' => 1,
            'total_wallet_minimum_use' => 0,
            'total_wallet_maximum_use' => 0,
            'total_wallet_sort_order' => 999
        ]);
    }

    public function uninstall(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/total/wallet')) {
            return;
        }

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('total_wallet');
    }
}
