<?php
namespace Opencart\System\Library\Ohbono;

/**
 * Creates wallet domain services from the OpenCart registry.
 *
 * Keeping construction in one place prevents controllers and event handlers
 * from duplicating dependency wiring.
 */
class WalletFactory
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function repository(): WalletRepository
    {
        return new WalletRepository($this->registry->get('db'));
    }

    public function logger(): WalletLogger
    {
        return new WalletLogger($this->registry->get('db'));
    }

    public function service(): WalletService
    {
        return new WalletService(
            $this->registry->get('db'),
            $this->repository(),
            $this->logger()
        );
    }
}
