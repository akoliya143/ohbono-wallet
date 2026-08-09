<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletTransaction extends \Opencart\System\Engine\Controller
{
    public function customer(): void
    {
        $customer_id = (int)($this->request->get['customer_id'] ?? 0);

        $this->response->redirect($this->url->link(
            'extension/ohbono/module/wallet_transaction',
            'user_token=' . $this->session->data['user_token'] .
            '&filter_customer=' . $customer_id
        ));
    }
}
