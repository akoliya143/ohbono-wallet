<?php
namespace Opencart\System\Library\Ohbono;

/**
 * Event integration helpers.
 *
 * Event handlers should call these methods instead of manipulating wallet
 * tables directly.
 */
class WalletEvent
{
    private $service;

    public function __construct(WalletService $service)
    {
        $this->service = $service;
    }

    public function customerAdded(int $customer_id): void
    {
        if ($customer_id <= 0) {
            return;
        }

        $this->service->createWallet($customer_id);
    }

    public function customerDeleted(int $customer_id): void
    {
        /*
         * Wallet deletion is intentionally not performed here.
         *
         * Wallets contain financial history and should not be physically
         * deleted as part of a customer-delete event. A future retention/
         * anonymisation policy will handle this safely.
         */
    }
}
