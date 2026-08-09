<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Payment;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getMethod(): array
    {
        $status = (int)$this->config->get('payment_wallet_status');

        return [
            'code' => 'wallet.wallet',
            'name' => $this->language->get('heading_title'),
            'status' => $status,
            'sort_order' => (int)$this->config->get('payment_wallet_sort_order')
        ];
    }

    /**
     * Wallet payment is available only when the wallet can cover the
     * complete current checkout total.
     */
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

        $total = $this->model_extension_ohbono_total_wallet->getCurrentCartTotal();
        $wallet_balance = $this->model_extension_ohbono_total_wallet->getAvailableBalance(
            (int)$this->customer->getId()
        );

        $wallet_use = $this->model_extension_ohbono_total_wallet->getSessionWalletUse();

        /*
         * If the wallet total has already been applied, the remaining
         * checkout total must be zero for this payment method.
         */
        $remaining = max(0, round($total - $wallet_use, 4));

        if ($remaining > 0.0001) {
            return [
                'success' => false,
                'error' => $this->language->get('error_insufficient')
            ];
        }

        if ($wallet_balance + 0.0001 < $wallet_use) {
            return [
                'success' => false,
                'error' => $this->language->get('error_balance_changed')
            ];
        }

        if ($wallet_use <= 0 || $wallet_use > $wallet_balance) {
            return [
                'success' => false,
                'error' => $this->language->get('error_amount')
            ];
        }

        return [
            'success' => true,
            'amount' => round($wallet_use, 4),
            'remaining' => 0.0
        ];
    }
}
