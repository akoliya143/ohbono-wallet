<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Module;

/**
 * OHBONO Wallet Checkout Model
 *
 * Read-only checkout calculations. This layer does not mutate the wallet.
 */
class WalletCheckout extends \Opencart\System\Engine\Model
{
    public function getWallet(int $customer_id): array
    {
        if ($customer_id <= 0) {
            return [];
        }

        $query = $this->db->query(
            "SELECT wallet_id, balance, status
             FROM `" . DB_PREFIX . "wallet`
             WHERE customer_id = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }

    public function getAvailableBalance(int $customer_id): float
    {
        $wallet = $this->getWallet($customer_id);

        if (!$wallet || !(int)$wallet['status']) {
            return 0.0;
        }

        return max(0.0, (float)$wallet['balance']);
    }

    public function calculateUsage(
        int $customer_id,
        float $order_total,
        float $requested_amount = 0.0
    ): array {
        $order_total = max(0.0, round($order_total, 4));
        $requested_amount = max(
            0.0,
            round($requested_amount, 4)
        );

        $balance = $this->getAvailableBalance($customer_id);

        if ($order_total <= 0.0 || $balance <= 0.0) {
            return [
                'wallet_balance' => $balance,
                'order_total' => $order_total,
                'requested_amount' => $requested_amount,
                'usable_amount' => 0.0,
                'remaining_total' => $order_total
            ];
        }

        $usable = min($balance, $order_total);

        if ($requested_amount > 0.0) {
            $usable = min($usable, $requested_amount);
        }

        return [
            'wallet_balance' => $balance,
            'order_total' => $order_total,
            'requested_amount' => $requested_amount,
            'usable_amount' => round($usable, 4),
            'remaining_total' => round(
                max(0.0, $order_total - $usable),
                4
            )
        ];
    }
}
