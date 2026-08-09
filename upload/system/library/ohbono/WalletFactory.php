<?php
namespace Opencart\System\Library\Ohbono;

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

    public function orderService(): WalletOrderService
    {
        return new WalletOrderService(
            $this->registry->get('db'),
            $this->service(),
            new WalletOrder($this->registry->get('db'))
        );
    }
}
