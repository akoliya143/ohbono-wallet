<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Payment;

use RuntimeException;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        if (!$this->customer->isLogged()) {
            return '';
        }

        if (!(int)$this->config->get('payment_wallet_status')) {
            return '';
        }

        $this->load->language('extension/ohbono/payment/wallet');
        $this->load->model('extension/ohbono/payment/wallet');

        $data['name'] = $this->language->get('heading_title');
        $data['code'] = 'wallet.wallet';

        return $this->load->view('extension/ohbono/payment/wallet', $data);
    }

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

        $this->json(
            $this->model_extension_ohbono_payment_wallet->validatePayment()
        );
    }

    /**
     * Called after an OpenCart order has an order_id.
     *
     * This is the authoritative server-side wallet debit entry point.
     */
    public function orderCreated(
        string &$route,
        array &$args,
        $output
    ): void {
        if (!$this->customer->isLogged()) {
            return;
        }

        if (empty($this->session->data['ohbono_wallet_payment'])) {
            return;
        }

        $order_id = $this->resolveOrderId($output, $args);

        if ($order_id <= 0) {
            return;
        }

        $this->load->model('extension/ohbono/payment/wallet');
        $this->load->model('extension/ohbono/checkout/wallet');

        $customer_id = (int)$this->customer->getId();

        $wallet_use = (float)($this->session->data['ohbono_wallet_use'] ?? 0);

        if ($wallet_use <= 0) {
            throw new RuntimeException('Wallet payment amount is missing.');
        }

        /*
         * Recalculate the order amount from the created order rather than
         * trusting the browser/session amount.
         */
        $order_query = $this->db->query(
            "SELECT `total`, `customer_id`
             FROM `" . DB_PREFIX . "order`
             WHERE `order_id` = '" . (int)$order_id . "'
             LIMIT 1"
        );

        if (!$order_query->num_rows) {
            throw new RuntimeException('Wallet payment order was not found.');
        }

        if ((int)$order_query->row['customer_id'] !== $customer_id) {
            throw new RuntimeException('Wallet customer validation failed.');
        }

        $order_total = round((float)$order_query->row['total'], 4);

        /*
         * For the dedicated Wallet payment method the wallet must cover
         * the complete order total. Therefore the authoritative debit is
         * the created order total, not an arbitrary client amount.
         */
        if ($order_total <= 0) {
            throw new RuntimeException('Wallet order total is invalid.');
        }

        if (abs($wallet_use - $order_total) > 0.0001) {
            /*
             * The wallet total may have changed after checkout. Do not
             * partially debit a Wallet-only order.
             */
            throw new RuntimeException('Wallet amount does not match the final order total.');
        }

        $this->model_extension_ohbono_checkout_wallet->debitForOrder(
            $order_id,
            $customer_id,
            $order_total
        );

        /*
         * Only clear wallet session state after the authoritative debit.
         */
        unset($this->session->data['ohbono_wallet_payment']);
        unset($this->session->data['ohbono_wallet_use']);
    }

    private function resolveOrderId($output, array $args): int
    {
        if (is_numeric($output)) {
            return (int)$output;
        }

        foreach ($args as $argument) {
            if (is_numeric($argument) && (int)$argument > 0) {
                return (int)$argument;
            }

            if (is_array($argument) && isset($argument['order_id'])) {
                return (int)$argument['order_id'];
            }
        }

        return 0;
    }

    private function json(array $output): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(
            json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
