<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletAudit extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_audit'
        );

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_audit'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_audit'
        );

        $filter_customer_id = (int)(
            $this->request->get['filter_customer_id'] ?? 0
        );

        $filter_admin_user_id = (int)(
            $this->request->get['filter_admin_user_id'] ?? 0
        );

        $filter_action = trim((string)(
            $this->request->get['filter_action'] ?? ''
        ));

        $data['heading_title'] =
            $this->language->get('heading_title');
        $data['text_no_results'] =
            $this->language->get('text_no_results');
        $data['text_filter'] =
            $this->language->get('text_filter');

        $data['filter_customer_id'] = $filter_customer_id;
        $data['filter_admin_user_id'] = $filter_admin_user_id;
        $data['filter_action'] = $filter_action;

        $data['audits'] =
            $this->model_extension_ohbono_module_wallet_audit
                ->getAudits(
                    $filter_customer_id,
                    $filter_admin_user_id,
                    $filter_action,
                    0,
                    100
                );

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_audit',
                $data
            )
        );
    }
}
