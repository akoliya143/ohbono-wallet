<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

/**
 * Wallet payment-method adapter.
 *
 * OpenCart payment-method selection should use this adapter only for
 * availability/presentation. Final deduction belongs to the payment service.
 */
class WalletPaymentMethod extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        if (!$this->customer->isLogged()) {
            return '';
        }

        $this->load->language(
            'extension/ohbono/module/wallet_payment'
        );

        $this->load->model(
            'extension/ohbono/module/wallet_payment'
        );

        $balance =
            $this->model_extension_ohbono_module_wallet_payment
                ->getBalance(
                    (int)$this->customer->getId()
                );

        if ($balance <= 0) {
            return '';
        }

        $currency = $this->session->data['currency'];

        return $this->load->view(
            'extension/ohbono/module/wallet_payment_method',
            [
                'title' => $this->language->get(
                    'text_wallet_payment'
                ),
                'balance' => $this->currency->format(
                    $balance,
                    $currency
                ),
                'balance_raw' => number_format(
                    $balance,
                    4,
                    '.',
                    ''
                )
            ]
        );
    }
}
