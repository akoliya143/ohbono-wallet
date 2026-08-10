<?php
/**
 * OHBONO Customer/Order Guard
 */
class OhbonoWalletCustomerOrderGuard
{
    public function assertOwnership(
        array $order,
        int $customer_id
    ): void {
        if ($customer_id <= 0 ||
            !$order ||
            (int)($order['order_id'] ?? 0) <= 0) {
            throw new \RuntimeException(
                'Valid customer and order are required.'
            );
        }

        if ((int)($order['customer_id'] ?? 0) !==
            $customer_id) {
            throw new \RuntimeException(
                'Order does not belong to customer.'
            );
        }
    }
}
