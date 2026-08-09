<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletMenu extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_menu');

        if (!$this->user->hasPermission('access', 'extension/ohbono/module/wallet_menu')) {
            return;
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['dashboard'] = $this->url->link(
            'extension/ohbono/module/wallet_dashboard',
            'user_token=' . $data['user_token']
        );

        $data['customers'] = $this->url->link(
            'extension/ohbono/module/wallet_customer',
            'user_token=' . $data['user_token']
        );

        $data['settings'] = $this->url->link(
            'extension/ohbono/module/wallet_settings',
            'user_token=' . $data['user_token']
        );

        $data['payment'] = $this->url->link(
            'extension/ohbono/payment/wallet',
            'user_token=' . $data['user_token']
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $this->response->setOutput(
            $this->load->view('extension/ohbono/module/wallet_menu', $data)
        );
    }

    public function install(): void
    {
        if (!$this->user->hasPermission('modify', 'extension/ohbono/module/wallet_menu')) {
            return;
        }

        $this->load->model('user/user_group');

        $user_group_id = (int)$this->user->getGroupId();

        $permissions = [
            'access',
            'modify'
        ];

        foreach ($permissions as $permission) {
            $this->model_user_user_group->addPermission(
                $user_group_id,
                $permission,
                'extension/ohbono/module/wallet_dashboard'
            );

            $this->model_user_user_group->addPermission(
                $user_group_id,
                $permission,
                'extension/ohbono/module/wallet_customer'
            );

            $this->model_user_user_group->addPermission(
                $user_group_id,
                $permission,
                'extension/ohbono/module/wallet_settings'
            );

            $this->model_user_user_group->addPermission(
                $user_group_id,
                $permission,
                'extension/ohbono/payment/wallet'
            );
        }
    }
}
