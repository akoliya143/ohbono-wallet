<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletPermissions extends \Opencart\System\Engine\Controller
{
    public function install(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet_permissions')) {
            return;
        }

        $this->load->model('user/user_group');

        $routes = [
            'extension/ohbono/module/wallet_dashboard',
            'extension/ohbono/module/wallet_customer',
            'extension/ohbono/module/wallet_settings',
            'extension/ohbono/payment/wallet'
        ];

        $groups = $this->getRelevantGroups();

        foreach ($groups as $user_group_id) {
            foreach ($routes as $route) {
                $this->model_user_user_group->addPermission(
                    (int)$user_group_id,
                    'access',
                    $route
                );

                $this->model_user_user_group->addPermission(
                    (int)$user_group_id,
                    'modify',
                    $route
                );
            }
        }
    }

    private function getRelevantGroups(): array
    {
        $query = $this->db->query(
            "SELECT `user_group_id`
             FROM `" . DB_PREFIX . "user_group`
             WHERE `name` IN ('Administrator', 'Super Administrator')"
        );

        $groups = [];

        foreach ($query->rows as $row) {
            $groups[] = (int)$row['user_group_id'];
        }

        return array_values(array_unique($groups));
    }
}
