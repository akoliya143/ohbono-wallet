<?php
namespace Opencart\Catalog\Controller\Event;

/**
 * OHBONO Wallet event adapter.
 *
 * This controller is intentionally small. Actual event registration should be
 * created through the OpenCart extension/event configuration for the target
 * 4.1.x installation.
 */
class OhbonoWallet extends \Opencart\System\Engine\Controller
{
    public function checkoutBefore(): void
    {
        /*
         * Do not deduct wallet funds here.
         *
         * This hook is for preparing checkout context only. Final wallet
         * capture belongs after the trusted order has been created and the
         * payment flow has established its final total.
         */
    }

    public function checkoutAfter(): void
    {
        /*
         * This hook is intentionally non-mutating.
         *
         * The actual payment callback should call the wallet service with the
         * authoritative order total and trusted order ID.
         */
    }

    public function orderAfter(): void
    {
        /*
         * Reserved for order/payment state synchronization.
         *
         * No balance changes should occur merely because an order event fires.
         */
    }
}
