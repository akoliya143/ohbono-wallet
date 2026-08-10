<?php
/**
 * OHBONO checkout callback guard.
 *
 * Verifies that the callback has a real OpenCart order belonging to the
 * authenticated customer before any wallet capture is attempted.
 */
class OhbonoWalletCheckoutCallbackGuard
{
    public function validateOrder(
        array $order,
        int $customer_id
    ): void {
        if ($customer_id <= 0) {
            throw new \RuntimeException(
                'Customer authentication is required.'
            );
        }

        if (!$order ||
            (int)($order['order_id'] ?? 0) <= 0) {
            throw new \RuntimeException(
                'A valid order is required.'
            );
        }

        if ((int)($order['customer_id'] ?? 0) !==
            $customer_id) {
            throw new \RuntimeException(
                'Order does not belong to customer.'
            );
        }

        if ((float)($order['total'] ?? 0) <= 0) {
            throw new \RuntimeException(
                'Order total is invalid.'
            );
        }
    }
}
