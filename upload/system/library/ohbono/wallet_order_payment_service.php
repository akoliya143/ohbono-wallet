<?php
/**
 * OHBONO Wallet Order Payment Service
 *
 * Coordinates wallet capture with an existing OpenCart order.
 *
 * This layer does not create orders. The caller must create/validate the
 * order first and pass the trusted final order total.
 */
class OhbonoWalletOrderPaymentService
{
    private $db;
    private $wallet_payment;

    public function __construct($db, $wallet_payment)
    {
        $this->db = $db;
        $this->wallet_payment = $wallet_payment;
    }

    public function captureForOrder(
        int $customer_id,
        int $order_id,
        float $order_total,
        float $wallet_amount,
        string $reference
    ): array {
        if ($customer_id <= 0 ||
            $order_id <= 0 ||
            $order_total <= 0 ||
            $wallet_amount <= 0 ||
            trim($reference) === '') {
            throw new \InvalidArgumentException(
                'Invalid order wallet payment request.'
            );
        }

        $order = $this->getOrder(
            $order_id,
            $customer_id
        );

        if (!$order) {
            throw new \RuntimeException(
                'Order not found.'
            );
        }

        $trusted_total = round(
            (float)$order['total'],
            4
        );

        if (abs($trusted_total - round($order_total, 4)) > 0.0001) {
            throw new \RuntimeException(
                'Order total changed. Recalculate checkout.'
            );
        }

        $wallet_amount = round(
            min(
                max(0.0, $wallet_amount),
                $trusted_total
            ),
            4
        );

        $this->validateOrderState(
            $order
        );

        $result =
            $this->wallet_payment->authorizeAndCapture(
                $customer_id,
                $trusted_total,
                $wallet_amount,
                $reference,
                $order_id
            );

        $remaining = round(
            max(
                0.0,
                $trusted_total - $result['amount']
            ),
            4
        );

        return [
            'transaction_id' =>
                (int)$result['transaction_id'],
            'wallet_amount' =>
                (float)$result['amount'],
            'remaining_amount' =>
                $remaining,
            'idempotent' =>
                !empty($result['idempotent'])
        ];
    }

    private function getOrder(
        int $order_id,
        int $customer_id
    ): array {
        $query = $this->db->query(
            "SELECT order_id,
                    customer_id,
                    total,
                    order_status_id,
                    currency_code
             FROM `" . DB_PREFIX . "order`
             WHERE order_id = '" .
                (int)$order_id . "'
             AND customer_id = '" .
                (int)$customer_id . "'
             LIMIT 1"
        );

        return $query->num_rows
            ? $query->row
            : [];
    }

    private function validateOrderState(
        array $order
    ): void {
        /*
         * A zero order_status_id represents an order that has not yet been
         * assigned a completed/processing status. The actual project can
         * extend this policy using its configured order status IDs.
         */
        if ((int)$order['order_status_id'] > 0) {
            throw new \RuntimeException(
                'Order is no longer payable.'
            );
        }
    }
}
