<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');

        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_login')
            ]);
            return;
        }

        $this->load->model('extension/ohbono/payment/wallet');

        $total = round((float)($this->request->post['total'] ?? 0), 4);

        try {
            $quote = $this->model_extension_ohbono_payment_wallet
                ->getQuote((int)$this->customer->getId(), $total);

            $this->json([
                'success' => true,
                'enabled' => $quote['available'] > 0 && $total > 0,
                'balance' => $quote['balance'],
                'maximum' => $quote['maximum'],
                'available' => $quote['available'],
                'applied' => $quote['applied'],
                'remaining' => $quote['remaining']
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getMethods(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');

        $methods = [];

        if ($this->customer->isLogged() &&
            $this->config->get('ohbono_wallet_status')) {

            $methods['ohbono_wallet'] = [
                'code' => 'ohbono_wallet',
                'name' => $this->language->get('text_title'),
                'sort_order' => (int)$this->config->get(
                    'ohbono_wallet_sort_order'
                ),
                'terms' => '',
                'title' => $this->language->get('text_title')
            ];
        }

        $this->json([
            'success' => true,
            'payment_methods' => $methods
        ]);
    }

    public function confirm(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');

        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_login')
            ]);
            return;
        }

        $this->load->model('extension/ohbono/payment/wallet');

        $total = round((float)($this->request->post['total'] ?? 0), 4);

        try {
            $quote = $this->model_extension_ohbono_payment_wallet
                ->getQuote((int)$this->customer->getId(), $total);

            if ($quote['applied'] <= 0) {
                throw new \RuntimeException(
                    $this->language->get('error_balance')
                );
            }

            $old = $this->session->data['ohbono_wallet_checkout'] ?? null;

            if ($old && !empty($old['transaction_id'])) {
                $this->json([
                    'success' => true,
                    'already_applied' => true,
                    'transaction_id' => (int)$old['transaction_id'],
                    'amount' => (float)$old['amount'],
                    'remaining' => (float)$old['remaining'],
                    'reference' => (string)$old['reference']
                ]);
                return;
            }

            $reference = 'CHECKOUT-' . bin2hex(random_bytes(12));

            $transaction_id =
                $this->model_extension_ohbono_payment_wallet->reserve(
                    (int)$this->customer->getId(),
                    $quote['applied'],
                    $reference
                );

            $this->session->data['ohbono_wallet_checkout'] = [
                'transaction_id' => $transaction_id,
                'customer_id' => (int)$this->customer->getId(),
                'amount' => $quote['applied'],
                'reference' => $reference,
                'order_total' => $total,
                'remaining' => $quote['remaining'],
                'created_at' => time()
            ];

            $this->json([
                'success' => true,
                'already_applied' => false,
                'transaction_id' => $transaction_id,
                'amount' => $quote['applied'],
                'remaining' => $quote['remaining'],
                'reference' => $reference
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function remove(): void
    {
        $this->load->language('extension/ohbono/payment/wallet');

        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => $this->language->get('error_login')
            ]);
            return;
        }

        $reservation =
            $this->session->data['ohbono_wallet_checkout'] ?? null;

        if (!$reservation) {
            $this->json([
                'success' => true,
                'removed' => false
            ]);
            return;
        }

        $this->load->library('ohbono/checkout');

        try {
            $result = $this->wallet_checkout->restoreReservation(
                (int)$this->customer->getId(),
                (int)$reservation['transaction_id'],
                (string)$reservation['reference']
            );

            unset($this->session->data['ohbono_wallet_checkout']);

            $this->json([
                'success' => true,
                'removed' => true,
                'restore_transaction_id' => $result['transaction_id'] ?? 0
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cleanup(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json(['success' => true, 'cleaned' => false]);
            return;
        }

        $reservation =
            $this->session->data['ohbono_wallet_checkout'] ?? null;

        if (!$reservation || empty($reservation['created_at'])) {
            $this->json(['success' => true, 'cleaned' => false]);
            return;
        }

        $ttl = (int)$this->config->get('ohbono_wallet_reservation_ttl');

        if ($ttl < 300) {
            $ttl = 1800;
        }

        if ((time() - (int)$reservation['created_at']) < $ttl) {
            $this->json(['success' => true, 'cleaned' => false]);
            return;
        }

        $this->load->library('ohbono/checkout');

        try {
            $this->wallet_checkout->restoreReservation(
                (int)$this->customer->getId(),
                (int)$reservation['transaction_id'],
                (string)$reservation['reference']
            );

            unset($this->session->data['ohbono_wallet_checkout']);

            $this->json([
                'success' => true,
                'cleaned' => true
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function json(array $data): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
