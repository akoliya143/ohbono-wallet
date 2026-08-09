<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Total;

class Wallet extends \Opencart\System\Engine\Model
{
    public function getTotal(array &$totals, array &$taxes, float &$total): void
    {
        if (!$this->customer->isLogged()) {
            return;
        }

        if (!(int)$this->config->get('ohbono_wallet_status') ||
            !(int)$this->config->get('ohbono_wallet_allow_checkout')) {
            return;
        }

        $requested = $this->getSessionWalletUse();

        if ($requested <= 0 || $total <= 0) {
            return;
        }

        $allowed = $this->calculateAllowedWalletUse($requested, $total);

        if ($allowed <= 0) {
            unset($this->session->data['ohbono_wallet_use']);
            return;
        }

        if (abs($allowed - $requested) > 0.00001) {
            $this->session->data['ohbono_wallet_use'] = $allowed;
        }

        $totals[] = [
            'extension' => 'ohbono',
            'code' => 'wallet',
            'title' => $this->language->get('text_wallet'),
            'value' => -$allowed,
            'sort_order' => (int)$this->config->get('ohbono_wallet_sort_order')
        ];

        $total = max(0, round($total - $allowed, 4));
    }

    public function getAvailableBalance(int $customer_id): float
    {
        if ($customer_id <= 0) {
            return 0.0;
        }

        $query = $this->db->query(
            "SELECT balance
             FROM `" . DB_PREFIX . "wallet`
             WHERE customer_id = '" . (int)$customer_id . "'
             AND status = '1'
             LIMIT 1"
        );

        return $query->num_rows
            ? round((float)$query->row['balance'], 4)
            : 0.0;
    }

    public function getSessionWalletUse(): float
    {
        return round(max(
            0,
            (float)($this->session->data['ohbono_wallet_use'] ?? 0)
        ), 4);
    }

    public function calculateAllowedWalletUse(
        float $requested,
        float $order_total
    ): float {
        if (!$this->customer->isLogged() ||
            $requested <= 0 ||
            $order_total <= 0) {
            return 0.0;
        }

        $balance = $this->getAvailableBalance(
            (int)$this->customer->getId()
        );

        $allowed = min(
            round($requested, 4),
            $balance,
            round($order_total, 4)
        );

        $maximum = (float)$this->config->get('ohbono_wallet_maximum_use');

        if ($maximum > 0) {
            $allowed = min($allowed, $maximum);
        }

        $minimum = (float)$this->config->get('ohbono_wallet_minimum_use');

        if ($allowed > 0 && $allowed < $minimum) {
            return 0.0;
        }

        return round(max(0, $allowed), 4);
    }

    public function getCurrentCartTotal(): float
    {
        $this->load->model('checkout/cart');

        $totals = [];
        $taxes = [];
        $total = 0.0;

        $this->model_checkout_cart->getTotals(
            $totals,
            $taxes,
            $total
        );

        return round(max(0, $total), 4);
    }

    public function clearSessionWallet(): void
    {
        unset(
            $this->session->data['ohbono_wallet_use'],
            $this->session->data['ohbono_wallet_order_id'],
            $this->session->data['ohbono_wallet_finalized']
        );
    }
}
