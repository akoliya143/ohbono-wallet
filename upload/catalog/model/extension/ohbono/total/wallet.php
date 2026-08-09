<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Total;

use Opencart\System\Library\Ohbono\WalletFactory;

class Wallet extends \Opencart\System\Engine\Model
{
    /**
     * Add the selected wallet amount as a negative order total.
     *
     * The wallet is never debited here. This method only changes the
     * calculated checkout total. The actual ledger debit is performed after
     * the order has been created and validated.
     */
    public function getTotal(array &$totals, array &$taxes, float &$total): void
    {
        if (!$this->customer->isLogged()) {
            return;
        }

        if (!(int)$this->config->get('total_wallet_status')) {
            return;
        }

        if (!(int)$this->config->get('total_wallet_allow_checkout')) {
            return;
        }

        $wallet_use = (float)($this->session->data['ohbono_wallet_use'] ?? 0);

        if ($wallet_use <= 0) {
            return;
        }

        $wallet_use = $this->calculateAllowedWalletUse($wallet_use, $total);

        if ($wallet_use <= 0) {
            $this->session->data['ohbono_wallet_use'] = 0.0;

            return;
        }

        $totals[] = [
            'extension' => 'ohbono',
            'code' => 'wallet',
            'title' => $this->language->get('text_wallet'),
            'value' => -$wallet_use,
            'sort_order' => (int)$this->config->get('total_wallet_sort_order')
        ];

        $total -= $wallet_use;
    }

    /**
     * Determine the maximum wallet amount that may be applied to this cart.
     */
    public function calculateAllowedWalletUse(float $requested, float $order_total): float
    {
        if (!$this->customer->isLogged() || $requested <= 0 || $order_total <= 0) {
            return 0.0;
        }

        $requested = round($requested, 4);
        $order_total = round($order_total, 4);

        $this->load->library('ohbono/WalletFactory');

        $factory = new WalletFactory($this->registry);
        $service = $factory->service();

        if (!$service->isEnabled()) {
            return 0.0;
        }

        $balance = $service->getBalance((int)$this->customer->getId());

        $maximum = min($requested, $balance, $order_total);

        $configured_maximum = (float)$this->config->get('total_wallet_maximum_use');

        if ($configured_maximum > 0) {
            $maximum = min($maximum, $configured_maximum);
        }

        $minimum = (float)$this->config->get('total_wallet_minimum_use');

        if ($maximum > 0 && $maximum < $minimum) {
            return 0.0;
        }

        return round(max(0.0, $maximum), 4);
    }
}
