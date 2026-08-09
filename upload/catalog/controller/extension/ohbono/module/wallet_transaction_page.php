<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletTransactionPage extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_transaction'
        );

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        $transaction_id = (int)(
            $this->request->get['transaction_id'] ?? 0
        );

        if ($transaction_id <= 0) {
            $this->response->redirect(
                $this->url->link(
                    'extension/ohbono/module/wallet_history'
                )
            );
            return;
        }

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['text_back'] = 'Back to Wallet History';

        $data['transaction_id'] = $transaction_id;

        $data['history'] = $this->url->link(
            'extension/ohbono/module/wallet_history'
        );

        $data['info_url'] = $this->url->link(
            'extension/ohbono/module/wallet_transaction/info'
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
                'extension/ohbono/module/wallet_transaction',
                $data
            )
        );
    }
}
