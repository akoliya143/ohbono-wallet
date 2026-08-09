<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Checkout;

use Opencart\System\Library\Ohbono\WalletFactory;
use RuntimeException;

class Wallet extends \Opencart\System\Engine\Model
{
    /**
     * Debit wallet for a newly-created order.
     *
     * This method is intentionally callable by an order integration/event.
     * The caller should perform it only after the order ID exists and before
     * the final payment state is considered complete.
     */
    public function debitForOrder(int $order_id, int $customer_id, float $amount): int
    {
        if ($order_id <= 0 || $customer_id <= 0 || $amount <= 0) {
            throw new RuntimeException('Invalid wallet order payment.');
        }

        $this->load->library('ohbono/WalletFactory');

        $factory = new WalletFactory($this->registry);
        $order_service = $factory->orderService();

        return $order_service->debitForOrder(
            $order_id,
            $customer_id,
            $amount,
            'ORDER-' . $order_id,
            'Wallet payment for order #' . $order_id
        );
    }

    public function isWalletFundedOrder(int $order_id): bool
    {
        $this->load->library('ohbono/WalletFactory');

        $factory = new WalletFactory($this->registry);

        return $factory->orderService()->isWalletFundedOrder($order_id);
    }
}
