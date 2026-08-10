<?php
/**
 * OHBONO Wallet Order State Guard
 *
 * Prevents wallet capture/reversal from being performed against an order in
 * an obviously invalid state. The exact OpenCart status IDs remain site
 * configuration and must be supplied by the caller.
 */
class OhbonoWalletOrderStateGuard
{
    public function assertCapturable(
        array $order,
        array $allowed_status_ids
    ): void {
        $status_id = (int)($order['order_status_id'] ?? 0);

        if ($status_id <= 0 ||
            !in_array($status_id, $allowed_status_ids, true)) {
            throw new \RuntimeException(
                'Order is not in a capturable state.'
            );
        }
    }

    public function assertRefundable(
        array $order,
        array $allowed_status_ids
    ): void {
        $status_id = (int)($order['order_status_id'] ?? 0);

        if ($status_id <= 0 ||
            !in_array($status_id, $allowed_status_ids, true)) {
            throw new \RuntimeException(
                'Order is not in a refundable state.'
            );
        }
    }
}
