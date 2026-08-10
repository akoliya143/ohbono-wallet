<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Event;

class Wallet extends \Opencart\System\Engine\Controller {
    public function accountLinks(string &$route, array &$args, mixed &$output): void {
        if (!$this->customer->isLogged() || !$this->config->get('ohbono_wallet_status')) return;
        $this->load->model('extension/ohbono/module/wallet_notifications');
        $count = $this->model_extension_ohbono_module_wallet_notifications->getUnreadCount((int)$this->customer->getId());
        if (is_array($output)) {
            $output['ohbono_wallet_notifications'] = [
                'name' => 'Wallet Notifications' . ($count ? ' (' . $count . ')' : ''),
                'href' => $this->url->link('extension/ohbono/module/wallet_notifications'),
                'sort_order' => 27
            ];
        }
    }
}
