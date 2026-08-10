<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletRefundHistory extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_refund_history'
        );

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_refund_history'
        );

        $customer_id = (int)$this->customer->getId();
        $page = max(
            1,
            (int)($this->request->get['page'] ?? 1)
        );

        $limit = 20;
        $start = ($page - 1) * $limit;

        $total =
            $this->model_extension_ohbono_module_wallet_refund_history
                ->getTotalRefunds($customer_id);

        $data['refunds'] =
            $this->model_extension_ohbono_module_wallet_refund_history
                ->getRefunds(
                    $customer_id,
                    $start,
                    $limit
                );

        $data['heading_title'] =
            $this->language->get('heading_title');
        $data['text_no_results'] =
            $this->language->get('text_no_results');
        $data['text_refund'] =
            $this->language->get('text_refund');

        $data['pagination'] = '';

        if ($total > $limit) {
            $pagination = new \Opencart\System\Library\Pagination();
            $pagination->total = $total;
            $pagination->page = $page;
            $pagination->limit = $limit;
            $pagination->url = $this->url->link(
                'extension/ohbono/module/wallet_refund_history',
                'page={page}'
            );

            $data['pagination'] =
                $pagination->render();
        }

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
                'extension/ohbono/module/wallet_refund_history',
                $data
            )
        );
    }
}
