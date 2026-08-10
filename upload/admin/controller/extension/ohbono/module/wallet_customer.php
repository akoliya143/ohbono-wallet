<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletCustomer extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/module/wallet_customer');

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_customer'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_customer'
        );

        $filter_name = (string)(
            $this->request->get['filter_name'] ?? ''
        );
        $filter_email = (string)(
            $this->request->get['filter_email'] ?? ''
        );

        $data['heading_title'] =
            $this->language->get('heading_title');
        $data['text_no_results'] =
            $this->language->get('text_no_results');
        $data['text_filter'] =
            $this->language->get('text_filter');

        $data['filter_name'] = $filter_name;
        $data['filter_email'] = $filter_email;

        $data['customers'] =
            $this->model_extension_ohbono_module_wallet_customer
                ->getCustomers(
                    $filter_name,
                    $filter_email,
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
                'extension/ohbono/module/wallet_customer',
                $data
            )
        );
    }

    public function info(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_customer'
        )) {
            $this->json(['success' => false]);
            return;
        }

        $customer_id = (int)(
            $this->request->get['customer_id'] ?? 0
        );

        $this->load->model(
            'extension/ohbono/module/wallet_customer'
        );

        $customer =
            $this->model_extension_ohbono_module_wallet_customer
                ->getCustomerWallet($customer_id);

        $this->json([
            'success' => !empty($customer),
            'customer' => $customer
        ]);
    }

    private function json(array $data): void
    {
        $this->response->addHeader(
            'Content-Type: application/json'
        );
        $this->response->setOutput(
            json_encode($data)
        );
    }
}
