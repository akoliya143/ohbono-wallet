<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Event;

class Checkout extends \Opencart\System\Engine\Controller
{
    public function orderAfter(
        string &$route,
        array &$args,
        mixed &$output
    ): void {
        if (!$this->customer->isLogged()) {
            return;
        }

        $reservation =
            $this->session->data['ohbono_wallet_checkout'] ?? null;

        if (!$reservation) {
            return;
        }

        $order_id = 0;

        if (isset($this->session->data['order_id'])) {
            $order_id = (int)$this->session->data['order_id'];
        }

        if (!$order_id && isset($args[0])) {
            $order_id = (int)$args[0];
        }

        if (!$order_id) {
            return;
        }

        $this->load->library('ohbono/checkout');

        try {
            $this->wallet_checkout->finalize(
                (int)$this->customer->getId(),
                $order_id,
                (int)$reservation['transaction_id'],
                (float)$reservation['amount'],
                (string)$reservation['reference']
            );

            unset($this->session->data['ohbono_wallet_checkout']);
        } catch (\Throwable $e) {
            $this->log->write(
                '[OHBONO] Wallet order finalization failed: ' .
                $e->getMessage()
            );
        }
    }

    public function checkoutFailure(): void
    {
        if (!$this->customer->isLogged()) {
            return;
        }

        $reservation =
            $this->session->data['ohbono_wallet_checkout'] ?? null;

        if (!$reservation) {
            return;
        }

        $this->load->library('ohbono/checkout');

        try {
            $this->wallet_checkout->restoreReservation(
                (int)$this->customer->getId(),
                (int)$reservation['transaction_id'],
                (string)$reservation['reference']
            );

            unset($this->session->data['ohbono_wallet_checkout']);
        } catch (\Throwable $e) {
            $this->log->write(
                '[OHBONO] Wallet reservation restoration failed: ' .
                $e->getMessage()
            );
        }
    }
}
