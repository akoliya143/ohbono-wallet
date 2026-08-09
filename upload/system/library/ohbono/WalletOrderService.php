<?php
namespace Opencart\System\Library\Ohbono;

use RuntimeException;

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
            round($amount, 4),
            WalletTransaction::TYPE_ORDER_PAYMENT,
            $comment !== '' ? $comment : 'Wallet payment for order #' . $order_id,
            $reference !== '' ? $reference : 'ORDER-' . $order_id,
            $order_id,
            0
        );

        $this->walletOrder->add(
            $order_id,
            $customer_id,
            round($amount, 4),
            $transaction_id
        );

        return $transaction_id;
    }

    /**
     * Refund the wallet-funded portion of an order.
     *
     * A separate credit transaction is created. The original debit is never
     * edited or deleted, preserving the wallet ledger.
     */
    public function refundOrder(
        int $order_id,
        string $reference = '',
        string $comment = ''
    ): int {
        $wallet_order = $this->walletOrder->get($order_id);

        if (!$wallet_order || (int)$wallet_order['status'] !== 1) {
            return 0;
        }

        $amount = round((float)$wallet_order['amount'], 4);

        if ($amount <= 0) {
            return 0;
        }

        $customer_id = (int)$wallet_order['customer_id'];

        $transaction_id = $this->walletService->credit(
            $customer_id,
            $amount,
            WalletTransaction::TYPE_REFUND,
            $comment !== '' ? $comment : 'Wallet refund for order #' . $order_id,
            $reference !== '' ? $reference : 'REFUND-' . $order_id,
            $order_id,
            0
        );

        $this->walletOrder->markRefunded($order_id);

        return $transaction_id;
    }

    public function getOrderWalletAmount(int $order_id): float
    {
        $wallet_order = $this->walletOrder->get($order_id);

        return $wallet_order ? round((float)$wallet_order['amount'], 4) : 0.0;
    }

    public function isRefunded(int $order_id): bool
    {
        $wallet_order = $this->walletOrder->get($order_id);

        return $wallet_order && (int)$wallet_order['status'] === 2;
    }

    public function isWalletFundedOrder(int $order_id): bool
    {
        return $this->walletOrder->exists($order_id);
    }
}
