<?php
namespace Opencart\System\Library\Ohbono;

use RuntimeException;

/**
 * Coordinates wallet payment state with an OpenCart order.
 *
 * The order itself must already exist before calling debitForOrder().
 * The caller is responsible for the surrounding database transaction.
 */
class WalletOrderService
{
    private $db;
    private $walletService;
    private $walletOrder;

    public function __construct($db, WalletService $walletService, WalletOrder $walletOrder)
    {
        $this->db = $db;
        $this->walletService = $walletService;
        $this->walletOrder = $walletOrder;
    }

    public function debitForOrder(
        int $order_id,
        int $customer_id,
        float $amount,
        string $reference = '',
        string $comment = ''
    ): int {
        if ($order_id <= 0 || $customer_id <= 0 || $amount <= 0) {
            throw new RuntimeException('Invalid wallet order debit.');
        }

        if ($this->walletOrder->exists($order_id)) {
            return 0;
        }

        $transaction_id = $this->walletService->debit(
            $customer_id,
            $amount,
            WalletTransaction::TYPE_ORDER_PAYMENT,
            $comment !== '' ? $comment : 'Wallet payment for order #' . $order_id,
            $reference !== '' ? $reference : 'ORDER-' . $order_id,
            $order_id,
            0
        );

        $this->walletOrder->add(
            $order_id,
            $customer_id,
            $amount,
            $transaction_id
        );

        return $transaction_id;
    }

    public function isWalletFundedOrder(int $order_id): bool
    {
        return $this->walletOrder->exists($order_id);
    }
}
