<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

/**
 * Optional checkout presentation block.
 *
 * The checkout integration should pass its calculated order total into this
 * controller. This controller never deducts wallet funds.
 */
class WalletCheckoutBlock extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        if (!$this->customer->isLogged()) {
            return '';
        }

        $this->load->language(
            'extension/ohbono/module/wallet_checkout'
        );

        $this->load->model(
            'extension/ohbono/module/wallet_checkout'
        );

        $customer_id = (int)$this->customer->getId();

        $order_total = max(
            0.0,
            (float)($this->request->get['order_total'] ?? 0)
        );

        $balance =
            $this->model_extension_ohbono_module_wallet_checkout
                ->getAvailableBalance($customer_id);

        if ($balance <= 0.0 || $order_total <= 0.0) {
            return '';
        }

        $currency = $this->session->data['currency'];

        $data['text_wallet'] =
            $this->language->get('text_wallet');
        $data['text_wallet_available'] =
            $this->language->get('text_wallet_available');
        $data['text_wallet_amount'] =
            $this->language->get('text_wallet_amount');
        $data['text_remaining_total'] =
            $this->language->get('text_remaining_total');
        $data['text_wallet_unavailable'] =
            $this->language->get('text_wallet_unavailable');

        $data['wallet_balance'] =
            $this->currency->format($balance, $currency);

        $data['wallet_balance_raw'] =
            number_format($balance, 4, '.', '');

        $data['wallet_max'] =
            number_format(
                min($balance, $order_total),
                4,
                '.',
                ''
            );

        $data['order_total'] =
            $this->currency->format(
                $order_total,
                $currency
            );

        $data['order_total_raw'] =
            number_format($order_total, 4, '.', '');

        return $this->load->view(
            'extension/ohbono/module/wallet_checkout',
            $data
        );
    }
}
