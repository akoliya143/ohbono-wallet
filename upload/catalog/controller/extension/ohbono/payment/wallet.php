<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Payment;

use RuntimeException;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        $this->load->language('extension/ohbono/payment/wallet');
        $this->load->model('extension/ohbono/payment/wallet');

        if (!$this->customer->isLogged()) {
            return '';
        }

        $method = $this->model_extension_ohbono_payment_wallet->getMethod();

        if (!$method['status']) {
            return '';
        }

        $data['name'] = $method['name'];
        $data['code'] = 'wallet';

        return $this->load->view('extension/ohbono/payment/wallet', $data);
    }

    /**
     * Called by checkout when the wallet payment method is selected.
     *
     * The server validates that the current order total can be covered
     * completely by the customer's wallet before returning success.
     */
    public function confirm(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');
        $this->load->model('extension/ohbono/payment/wallet');

        try {
            if (!$this->customer->isLogged()) {
                throw new RuntimeException($this->language->get('error_login'));
            }

            $result = $this->model_extension_ohbono_payment_wallet->validatePayment();

            if (!$result['success']) {
                throw new RuntimeException($result['error']);
            }

            /*
             * The wallet is not debited by the browser request.
             *
             * The order integration performs the authoritative debit after
             * an order ID exists. This endpoint only validates the payment
             * method and stores the selected method in the checkout session.
             */
            $this->session->data['payment_method'] = [
                'code' => 'wallet.wallet',
                'name' => $this->language->get('heading_title')
            ];

            $this->session->data['ohbono_wallet_payment'] = true;

            $this->json([
                'success' => true,
                'message' => $this->language->get('text_payment_ready')
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function validate(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');
        $this->load->model('extension/ohbono/payment/wallet');

        $result = $this->model_extension_ohbono_payment_wallet->validatePayment();

        $this->json($result);
    }

    private function json(array $output): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(
            json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
