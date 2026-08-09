<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Checkout;

class Wallet extends \Opencart\System\Engine\Controller
{
    public function apply(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'error' => 'Please login to use your wallet.'
            ]);
            return;
        }

        if (!(int)$this->config->get('ohbono_wallet_status') ||
            !(int)$this->config->get('ohbono_wallet_allow_checkout')) {
            $this->json([
                'success' => false,
                'error' => 'Wallet payment is currently unavailable.'
            ]);
            return;
        }

        $requested = round(
            (float)($this->request->post['amount'] ?? 0),
            4
        );

        if ($requested <= 0) {
            $this->json([
                'success' => false,
                'error' => 'Please enter a valid wallet amount.'
            ]);
            return;
        }

        $this->load->model('extension/ohbono/total/wallet');

        $cart_total = $this->model_extension_ohbono_total_wallet
            ->getCurrentCartTotal();

        $allowed = $this->model_extension_ohbono_total_wallet
            ->calculateAllowedWalletUse($requested, $cart_total);

        if ($allowed <= 0) {
            $this->json([
                'success' => false,
                'error' => 'The requested wallet amount cannot be applied.',
                'balance' => $this->model_extension_ohbono_total_wallet
                    ->getAvailableBalance((int)$this->customer->getId())
            ]);
            return;
        }

        $this->session->data['ohbono_wallet_use'] = $allowed;

        $this->json([
            'success' => true,
            'wallet_used' => $allowed,
            'wallet_balance' => $this->model_extension_ohbono_total_wallet
                ->getAvailableBalance((int)$this->customer->getId()),
            'cart_total' => $cart_total,
            'remaining' => round(max(0, $cart_total - $allowed), 4),
            'message' => 'Wallet amount applied successfully.'
        ]);
    }

    public function remove(): void
    {
        unset($this->session->data['ohbono_wallet_use']);

        $this->json([
            'success' => true,
            'wallet_used' => 0,
            'message' => 'Wallet amount removed.'
        ]);
    }

    public function info(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false
            ]);
            return;
        }

        $this->load->model('extension/ohbono/total/wallet');

        $cart_total = $this->model_extension_ohbono_total_wallet
            ->getCurrentCartTotal();

        $balance = $this->model_extension_ohbono_total_wallet
            ->getAvailableBalance((int)$this->customer->getId());

        $used = $this->model_extension_ohbono_total_wallet
            ->getSessionWalletUse();

        $allowed = $this->model_extension_ohbono_total_wallet
            ->calculateAllowedWalletUse($used, $cart_total);

        if ($allowed != $used) {
            if ($allowed > 0) {
                $this->session->data['ohbono_wallet_use'] = $allowed;
            } else {
                unset($this->session->data['ohbono_wallet_use']);
            }
        }

        $this->json([
            'success' => true,
            'balance' => $balance,
            'wallet_used' => $allowed,
            'cart_total' => $cart_total,
            'remaining' => round(max(0, $cart_total - $allowed), 4)
        ]);
    }

    private function json(array $data): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
