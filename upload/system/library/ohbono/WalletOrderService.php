<?php
namespace Opencart\System\Library\Ohbono;

use RuntimeException;

/**
 * Final order-level wallet debit coordinator.
 *
 * The debit is idempotent at order level. A wallet_order row is created
 * only after WalletService successfully creates the ledger transaction.
 *
 * IMPORTANT:
 * The caller should invoke this from the order-created integration before
 * treating the wallet-funded payment as completed.
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

        /*
         * WalletService performs the authoritative balance check and
         * row-level wallet lock. Never trust the checkout/session amount
         * without this second server-side validation.
         */
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

    public function isWalletFundedOrder(int $order_id): bool
    {
        return $this->walletOrder->exists($order_id);
    }

    public function getWalletOrder(int $order_id): array
    {
        if ($order_id <= 0) {
            return [];
        }

        $query = $this->db->query(
            "SELECT *
             FROM `" . DB_PREFIX . "wallet_order`
             WHERE `order_id` = '" . (int)$order_id . "'
             LIMIT 1"
        );

        return $query->num_rows ? $query->row : [];
    }
}
