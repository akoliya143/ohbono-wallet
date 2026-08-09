<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getMethod(): array
    {
        return [
            'code' => 'wallet.wallet',
            'name' => $this->language->get('heading_title'),
            'status' => (int)$this->config->get('payment_wallet_status'),
            'sort_order' => (int)$this->config->get('payment_wallet_sort_order')
        ];
    }

    public function validatePayment(): array
    {
        if (!$this->customer->isLogged()) {
            return [
                'success' => false,
                'error' => $this->language->get('error_login')
            ];
        }

        if (!(int)$this->config->get('payment_wallet_status')) {
            return [
                'success' => false,
                'error' => $this->language->get('error_disabled')
            ];
        }

        $this->load->model('extension/ohbono/total/wallet');

        $cart_total = $this->model_extension_ohbono_total_wallet->getCurrentCartTotal();
        $wallet_use = $this->model_extension_ohbono_total_wallet->getSessionWalletUse();
        $balance = $this->model_extension_ohbono_total_wallet->getAvailableBalance(
            (int)$this->customer->getId()
        );

        if ($cart_total <= 0) {
            return [
                'success' => false,
                'error' => $this->language->get('error_amount')
            ];
        }

        if ($wallet_use <= 0 || abs($wallet_use - $cart_total) > 0.0001) {
            return [
                'success' => false,
                'error' => $this->language->get('error_insufficient')
            ];
        }

        if ($balance + 0.0001 < $cart_total) {
            return [
                'success' => false,
                'error' => $this->language->get('error_balance_changed')
            ];
        }

        return [
            'success' => true,
            'amount' => round($cart_total, 4),
            'remaining' => 0.0
        ];
    }
}
