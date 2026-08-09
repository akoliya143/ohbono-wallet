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

    public function refundOrder(int $order_id): int
    {
        if ($order_id <= 0) {
            return 0;
        }

        $this->load->library('ohbono/WalletFactory');

        $factory = new WalletFactory($this->registry);

        return $factory->orderService()->refundOrder(
            $order_id,
            'REFUND-' . $order_id,
            'Wallet refund for order #' . $order_id
        );
    }

    public function getOrderWalletAmount(int $order_id): float
    {
        $this->load->library('ohbono/WalletFactory');

        return $this->registry->get('ohbono_wallet_factory')
            ? 0.0
            : (new WalletFactory($this->registry))->orderService()->getOrderWalletAmount($order_id);
    }
}
