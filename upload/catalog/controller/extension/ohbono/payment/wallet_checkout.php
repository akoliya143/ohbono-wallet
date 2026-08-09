<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Payment;

class WalletCheckout extends \Opencart\System\Engine\Controller
{
    public function apply(): void
    {
        $this->response->addHeader('Content-Type: application/json');

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Please log in before using wallet payment.'
            ]));
            return;
        }

        $this->load->model('extension/ohbono/payment/wallet');

        $customer_id = (int)$this->customer->getId();
        $total = round((float)($this->request->post['total'] ?? 0), 4);

        try {
            $quote = $this->model_extension_ohbono_payment_wallet
                ->getQuote($customer_id, $total);

            if ($quote['applied'] <= 0) {
                throw new \RuntimeException(
                    'No wallet amount is available for this order.'
                );
            }

            $reference = 'CHECKOUT-' . bin2hex(random_bytes(8));

            $transaction_id = $this->model_extension_ohbono_payment_wallet
                ->reserve(
                    $customer_id,
                    $quote['applied'],
                    $reference
                );

            $this->session->data['ohbono_wallet_checkout'] = [
                'transaction_id' => $transaction_id,
                'customer_id' => $customer_id,
                'amount' => $quote['applied'],
                'reference' => $reference,
                'order_total' => $total,
                'remaining' => $quote['remaining']
            ];

            $this->response->setOutput(json_encode([
                'success' => true,
                'transaction_id' => $transaction_id,
                'amount' => $quote['applied'],
                'remaining' => $quote['remaining'],
                'reference' => $reference
            ]));
        } catch (\Throwable $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }

    public function restore(): void
    {
        $this->response->addHeader('Content-Type: application/json');

        if (!$this->customer->isLogged()) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Please log in.'
            ]));
            return;
        }

        $wallet = $this->session->data['ohbono_wallet_checkout'] ?? null;

        if (!$wallet || empty($wallet['amount']) || empty($wallet['reference'])) {
            $this->response->setOutput(json_encode([
                'success' => true,
                'restored' => false
            ]));
            return;
        }

        $this->load->model('extension/ohbono/payment/wallet');

        try {
            $restore_reference =
                $wallet['reference'] . '-RESTORE';

            $transaction_id = $this->model_extension_ohbono_payment_wallet
                ->restore(
                    (int)$this->customer->getId(),
                    (float)$wallet['amount'],
                    $restore_reference
                );

            unset($this->session->data['ohbono_wallet_checkout']);

            $this->response->setOutput(json_encode([
                'success' => true,
                'restored' => true,
                'transaction_id' => $transaction_id
            ]));
        } catch (\Throwable $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
}
