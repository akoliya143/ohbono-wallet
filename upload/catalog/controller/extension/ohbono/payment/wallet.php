<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $this->language->get('error_login')
            ]));
            return;
        }

        $this->load->model('extension/ohbono/payment/wallet');

        $customer_id = (int)$this->customer->getId();
        $total = round((float)($this->request->post['total'] ?? 0), 4);

        try {
            $quote = $this->model_extension_ohbono_payment_wallet
                ->getQuote($customer_id, $total);

            $this->response->addHeader('Content-Type: application/json');

            $this->response->setOutput(json_encode([
                'success' => true,
                'balance' => $quote['balance'],
                'maximum' => $quote['maximum'],
                'available' => $quote['available'],
                'applied' => $quote['applied'],
                'remaining' => $quote['remaining']
            ]));
        } catch (\Throwable $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
}
