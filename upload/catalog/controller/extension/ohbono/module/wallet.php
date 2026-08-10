<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet'
        );

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        if (!$this->config->get('ohbono_wallet_status')) {
            $this->response->redirect(
                $this->url->link('account/account')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet'
        );

        $customer_id = (int)$this->customer->getId();
        $currency = $this->session->data['currency'];

        $balance =
            $this->model_extension_ohbono_module_wallet
                ->getBalance($customer_id);

        $data['heading_title'] =
            $this->language->get('heading_title');
        $data['text_available_balance'] =
            $this->language->get('text_available_balance');
        $data['text_view_history'] =
            $this->language->get('text_view_history');
        $data['text_view_preferences'] =
            $this->language->get('text_view_preferences');

        $data['balance'] =
            $this->currency->format(
                (float)$balance,
                $currency
            );

        $data['history'] = $this->url->link(
            'extension/ohbono/module/wallet_history'
        );

        $data['preferences'] = $this->url->link(
            'extension/ohbono/module/wallet_preferences'
        );

        $data['header'] =
            $this->load->controller('common/header');
        $data['footer'] =
            $this->load->controller('common/footer');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['column_right'] =
            $this->load->controller('common/column_right');
        $data['content_top'] =
            $this->load->controller('common/content_top');
        $data['content_bottom'] =
            $this->load->controller('common/content_bottom');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_dashboard',
                $data
            )
        );
    }
}
