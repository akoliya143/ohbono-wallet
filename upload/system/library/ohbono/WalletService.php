<?php
namespace Opencart\System\Library\Ohbono;

/**
 * Main wallet domain service.
 */
class WalletService
{
    private $db;
    private $repository;
    private $logger;

    public function __construct($db, WalletRepository $repository, WalletLogger $logger)
    {
        $this->db = $db;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function createWallet(int $customer_id): int
    {
        return $this->repository->create($customer_id);
    }

    public function getBalance(int $customer_id): float
    {
        if ($customer_id <= 0) {
            return 0.0;
        }

        $wallet = $this->repository->getByCustomerId($customer_id);

        if (!$wallet) {
            return 0.0;
        }

        return WalletHelper::amount($wallet['balance']);
    }

    public function isEnabled(): bool
    {
        return (bool)(int)$this->repository->getSetting('status', '1');
    }

    public function canSpend(int $customer_id, float $amount): bool
    {
        if ($customer_id <= 0 || $amount <= 0 || !$this->isEnabled()) {
            return false;
        }

        return $this->getBalance($customer_id) >= WalletHelper::amount($amount);
    }

    public function credit(
        int $customer_id,
        float $amount,
        string $type = WalletTransaction::TYPE_ADMIN_CREDIT,
        string $comment = '',
        string $reference = '',
        int $order_id = 0,
        int $created_by = 0
    ): int {
        $amount = WalletHelper::positiveAmount($amount);

        if ($customer_id <= 0) {
            throw new WalletException('Invalid customer ID.');
        }

        $this->beginTransaction();

        try {
            $wallet = $this->repository->getForUpdate($customer_id);

            if (!$wallet) {
                $this->repository->create($customer_id);
                $wallet = $this->repository->getForUpdate($customer_id);
            }

            if (!$wallet) {
                throw new WalletException('Unable to create or load wallet.');
            }

            if (!(int)$wallet['status']) {
                throw new WalletException('Wallet is disabled.');
            }

            $before = WalletHelper::amount($wallet['balance']);
            $after = WalletHelper::amount($before + $amount);

            $transaction_id = $this->repository->insertTransaction([
                'wallet_id' => (int)$wallet['wallet_id'],
                'customer_id' => $customer_id,
                'order_id' => $order_id,
                'type' => WalletHelper::transactionType($type),
                'direction' => WalletTransaction::DIRECTION_CREDIT,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $reference,
                'comment' => $comment,
                'created_by' => $created_by
            ]);

            $this->repository->updateBalance((int)$wallet['wallet_id'], $after);

            $this->commit();

            return $transaction_id;
        } catch (\Throwable $e) {
            $this->rollback();

            $this->logger->error(
                'Wallet credit failed.',
                $customer_id,
                [
                    'amount' => $amount,
                    'type' => $type,
                    'error' => $e->getMessage()
                ]
            );

            if ($e instanceof WalletException) {
                throw $e;
            }

            throw new WalletException('Unable to credit wallet.');
        }
    }

    public function debit(
        int $customer_id,
        float $amount,
        string $type = WalletTransaction::TYPE_ADMIN_DEBIT,
        string $comment = '',
        string $reference = '',
        int $order_id = 0,
        int $created_by = 0
    ): int {
        $amount = WalletHelper::positiveAmount($amount);

        if ($customer_id <= 0) {
            throw new WalletException('Invalid customer ID.');
        }

        $this->beginTransaction();

        try {
            $wallet = $this->repository->getForUpdate($customer_id);

            if (!$wallet) {
                throw new WalletException('Wallet does not exist.');
            }

            if (!(int)$wallet['status']) {
                throw new WalletException('Wallet is disabled.');
            }

            $before = WalletHelper::amount($wallet['balance']);

            if ($before < $amount) {
                throw new WalletException('Insufficient wallet balance.');
            }

            $after = WalletHelper::amount($before - $amount);

            $transaction_id = $this->repository->insertTransaction([
                'wallet_id' => (int)$wallet['wallet_id'],
                'customer_id' => $customer_id,
                'order_id' => $order_id,
                'type' => WalletHelper::transactionType($type),
                'direction' => WalletTransaction::DIRECTION_DEBIT,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $reference,
                'comment' => $comment,
                'created_by' => $created_by
            ]);

            $this->repository->updateBalance((int)$wallet['wallet_id'], $after);

            $this->commit();

            return $transaction_id;
        } catch (\Throwable $e) {
            $this->rollback();

            $this->logger->error(
                'Wallet debit failed.',
                $customer_id,
                [
                    'amount' => $amount,
                    'type' => $type,
                    'error' => $e->getMessage()
                ]
            );

            if ($e instanceof WalletException) {
                throw $e;
            }

            throw new WalletException('Unable to debit wallet.');
        }
    }

    /**
     * Debit wallet balance for an order exactly once.
     *
     * The wallet debit and wallet_order mapping are committed together.
     * This prevents the dangerous state where money is deducted but the
     * order mapping is missing, which could otherwise cause a second debit
     * if the order event is executed again.
     */
    public function debitForOrder(
        int $order_id,
        int $customer_id,
        float $amount,
        string $reference = '',
        string $comment = ''
    ): int {
        $amount = WalletHelper::positiveAmount($amount);

        if ($order_id <= 0 || $customer_id <= 0) {
            throw new WalletException('Invalid order or customer ID.');
        }

        $this->beginTransaction();

        try {
            $existing = $this->repository->getWalletOrderByOrderId($order_id);

            if ($existing) {
                $this->commit();

                return (int)$existing['transaction_id'];
            }

            $wallet = $this->repository->getForUpdate($customer_id);

            if (!$wallet) {
                throw new WalletException('Wallet does not exist.');
            }

            if (!(int)$wallet['status']) {
                throw new WalletException('Wallet is disabled.');
            }

            $before = WalletHelper::amount($wallet['balance']);

            if ($before < $amount) {
                throw new WalletException('Insufficient wallet balance.');
            }

            $after = WalletHelper::amount($before - $amount);

            $transaction_id = $this->repository->insertTransaction([
                'wallet_id' => (int)$wallet['wallet_id'],
                'customer_id' => $customer_id,
                'order_id' => $order_id,
                'type' => WalletTransaction::TYPE_ORDER_PAYMENT,
                'direction' => WalletTransaction::DIRECTION_DEBIT,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $reference,
                'comment' => $comment,
                'created_by' => 0
            ]);

            $this->repository->updateBalance((int)$wallet['wallet_id'], $after);

            $this->repository->createWalletOrder(
                $order_id,
                $customer_id,
                $transaction_id,
                $amount
            );

            $this->commit();

            return $transaction_id;
        } catch (\Throwable $e) {
            $this->rollback();

            $this->logger->error(
                'Wallet order debit failed.',
                $customer_id,
                [
                    'order_id' => $order_id,
                    'amount' => $amount,
                    'error' => $e->getMessage()
                ]
            );

            if ($e instanceof WalletException) {
                throw $e;
            }

            throw new WalletException('Unable to apply wallet payment.');
        }
    }

    public function getTransactions(int $customer_id, int $start = 0, int $limit = 20): array
    {
        return $this->repository->getTransactions($customer_id, $start, $limit);
    }

    public function getTransactionCount(int $customer_id): int
    {
        return $this->repository->getTransactionCount($customer_id);
    }

    private function beginTransaction(): void
    {
        $this->db->query('START TRANSACTION');
    }

    private function commit(): void
    {
        $this->db->query('COMMIT');
    }

    private function rollback(): void
    {
        $this->db->query('ROLLBACK');
    }
}
