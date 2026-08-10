<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletPermissions extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_permissions'
        )) {
            $this->response->setOutput(
                'Permission denied.'
            );
            return;
        }

        $this->load->model('user/user_group');

        $groups = $this->model_user_user_group
            ->getUserGroups();

        $routes = [
            'extension/ohbono/module/wallet_reconciliation',
            'extension/ohbono/module/wallet_refund',
            'extension/ohbono/module/wallet_diagnostics'
        ];

        foreach ($groups as $group) {
            $permissions = [
                'access' => $routes,
                'modify' => $routes
            ];

            $this->model_user_user_group->addPermission(
                (int)$group['user_group_id'],
                'access',
                'extension/ohbono/module/wallet_reconciliation'
            );

            $this->model_user_user_group->addPermission(
                (int)$group['user_group_id'],
                'modify',
                'extension/ohbono/module/wallet_refund'
            );

            $this->model_user_user_group->addPermission(
                (int)$group['user_group_id'],
                'access',
                'extension/ohbono/module/wallet_diagnostics'
            );
        }

        $this->response->setOutput(
            'OHBONO Wallet permissions initialized.'
        );
    }
}
