<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletHistory extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_history'
        );

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_history'
        );

        $customer_id = (int)$this->customer->getId();

        $data['heading_title'] =
            $this->language->get('heading_title');
        $data['text_no_results'] =
            $this->language->get('text_no_results');
        $data['text_credit'] =
            $this->language->get('text_credit');
        $data['text_debit'] =
            $this->language->get('text_debit');

        $data['transactions'] =
            $this->model_extension_ohbono_module_wallet_history
                ->getTransactions(
                    $customer_id,
                    0,
                    100
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
                'extension/ohbono/module/wallet_history',
                $data
            )
        );
    }
}
