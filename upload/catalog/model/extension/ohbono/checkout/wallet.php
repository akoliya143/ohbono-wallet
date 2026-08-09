<?php
namespace Opencart\Catalog\Model\Extension\Ohbono\Checkout;

use Opencart\System\Library\Ohbono\WalletFactory;
use RuntimeException;

class Wallet extends \Opencart\System\Engine\Model
{
    public function debitForOrder(int $order_id, int $customer_id, float $amount): int
    {
        if ($order_id <= 0 || $customer_id <= 0 || $amount <= 0) {
            throw new RuntimeException('Invalid wallet order payment.');
        }

        $this->load->library('ohbono/WalletFactory');

        $factory = new WalletFactory($this->registry);

        return $factory->orderService()->debitForOrder(
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

    public function getWalletOrder(int $order_id): array
    {
        $this->load->library('ohbono/WalletFactory');

        $factory = new WalletFactory($this->registry);

        return $factory->orderService()->getWalletOrder($order_id);
    }
}
