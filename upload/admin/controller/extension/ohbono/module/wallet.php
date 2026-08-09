<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function install(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet')) {
            return;
        }

        $this->load->model('setting/event');

        $events = [
            [
                'code' => 'ohbono_wallet_customer_add',
                'trigger' => 'catalog/model/account/customer/addCustomer/after',
                'action' => 'extension/ohbono/module/wallet.customerAdd',
                'description' => 'Create an OHBONO wallet after customer creation.',
                'status' => 1,
                'sort_order' => 1
            ]
        ];

        foreach ($events as $event) {
            $this->model_setting_event->deleteEventByCode($event['code']);

            $this->model_setting_event->addEvent(
                $event['code'],
                $event['description'],
                $event['trigger'],
                $event['action'],
                $event['status'],
                $event['sort_order']
            );
        }
    }
}
